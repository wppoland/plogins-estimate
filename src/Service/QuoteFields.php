<?php

declare(strict_types=1);

namespace Estimate\Service;

defined('ABSPATH') || exit;

/**
 * Extra quote request form fields contributed by other plugins.
 *
 * This is the extension point add-ons use to append qualification questions
 * (budget, timeline, industry, consent and so on) to the [estimate_quote]
 * request form. The FREE plugin declares no fields of its own: everything here
 * comes from the `estimate/quote_form_fields` filter.
 *
 * Declaring fields:
 *
 *     add_filter('estimate/quote_form_fields', function (array $fields): array {
 *         $fields[] = [
 *             'key'         => 'budget',
 *             'label'       => __('Budget', 'my-addon'),
 *             'type'        => 'select',
 *             'options'     => [
 *                 'under_5k' => __('Under 5,000', 'my-addon'),
 *                 'over_5k'  => __('5,000 or more', 'my-addon'),
 *             ],
 *             'required'    => true,
 *             'description' => __('Helps us size the proposal.', 'my-addon'),
 *         ];
 *
 *         return $fields;
 *     });
 *
 * Field contract (one array per field):
 *
 * - key         string  Required. Unique field identifier, run through
 *                       sanitize_key(). Becomes the input name
 *                       (estimate_field_<key>) and the key the answer is stored
 *                       and read back under. Duplicate keys: first one wins.
 * - label       string  Required. Human-readable, already translated. Plain
 *                       text (tags are stripped). A field without a label is
 *                       dropped, because it could not be labelled accessibly.
 * - type        string  Optional, defaults to 'text'. One of 'text',
 *                       'textarea', 'select', 'checkbox'. Unknown types fall
 *                       back to 'text'.
 * - options     array   Required for 'select', ignored otherwise. Map of
 *                       machine value => translated label. A select with no
 *                       usable options is dropped.
 * - required    bool    Optional, defaults to false. Enforced server side.
 * - description string  Optional. Short help text rendered under the field and
 *                       wired up with aria-describedby.
 *
 * Anything the filter returns that does not satisfy the contract is dropped,
 * and only declared keys are ever read from the POST body.
 */
final class QuoteFields
{
    /** Prefix for the input name of every add-on field. */
    public const INPUT_PREFIX = 'estimate_field_';

    /** Supported field types. */
    private const TYPES = ['text', 'textarea', 'select', 'checkbox'];

    /**
     * Normalised field definitions, resolved once per request.
     *
     * @var array<string, array{key: string, label: string, type: string, options: array<string, string>, required: bool, description: string}>|null
     */
    private ?array $fields = null;

    /**
     * Declared fields, normalised and keyed by field key.
     *
     * @return array<string, array{key: string, label: string, type: string, options: array<string, string>, required: bool, description: string}>
     */
    public function all(): array
    {
        if (null !== $this->fields) {
            return $this->fields;
        }

        /**
         * Filter the extra fields appended to the quote request form.
         *
         * Each entry is an array describing one field. See the QuoteFields
         * class docblock for the full contract.
         *
         * @param array<int, array<string, mixed>> $fields Field definitions.
         */
        $declared = apply_filters('estimate/quote_form_fields', []);

        $fields = [];

        if (is_array($declared)) {
            foreach ($declared as $declaration) {
                if (! is_array($declaration)) {
                    continue;
                }

                $field = $this->normalise($declaration);

                if (null === $field || isset($fields[$field['key']])) {
                    continue;
                }

                $fields[$field['key']] = $field;
            }
        }

        return $this->fields = $fields;
    }

    public function hasFields(): bool
    {
        return [] !== $this->all();
    }

    /**
     * Turn one declaration into a usable field, or null if it is unusable.
     *
     * @param array<string, mixed> $declaration
     * @return array{key: string, label: string, type: string, options: array<string, string>, required: bool, description: string}|null
     */
    private function normalise(array $declaration): ?array
    {
        $key = isset($declaration['key']) && is_scalar($declaration['key'])
            ? sanitize_key((string) $declaration['key'])
            : '';

        if ('' === $key) {
            return null;
        }

        $label = isset($declaration['label']) && is_scalar($declaration['label'])
            ? trim(wp_strip_all_tags((string) $declaration['label']))
            : '';

        if ('' === $label) {
            return null;
        }

        $type = isset($declaration['type']) && is_scalar($declaration['type'])
            ? strtolower(trim((string) $declaration['type']))
            : 'text';

        if (! in_array($type, self::TYPES, true)) {
            $type = 'text';
        }

        $options = [];

        if ('select' === $type) {
            $options = $this->normaliseOptions($declaration['options'] ?? null);

            if ([] === $options) {
                return null;
            }
        }

        $description = isset($declaration['description']) && is_scalar($declaration['description'])
            ? trim(wp_strip_all_tags((string) $declaration['description']))
            : '';

        return [
            'key'         => $key,
            'label'       => $label,
            'type'        => $type,
            'options'     => $options,
            'required'    => ! empty($declaration['required']),
            'description' => $description,
        ];
    }

    /**
     * @return array<string, string> Machine value => label.
     */
    private function normaliseOptions(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $options = [];

        foreach ($raw as $value => $label) {
            if (! is_scalar($value) || ! is_scalar($label)) {
                continue;
            }

            $optionValue = sanitize_text_field((string) $value);

            if ('' === $optionValue || isset($options[$optionValue])) {
                continue;
            }

            $optionLabel = trim(wp_strip_all_tags((string) $label));

            $options[$optionValue] = '' !== $optionLabel ? $optionLabel : $optionValue;
        }

        return $options;
    }

    /**
     * Sanitise the submitted answers, one sanitiser per field type.
     *
     * Only declared keys are used: anything else in $posted is ignored.
     *
     * @param array<string, string> $posted Field key => value, already read
     *                                      from the nonce-verified request.
     * @return array<string, string> Field key => sanitised value.
     */
    public function sanitizeSubmission(array $posted): array
    {
        $values = [];

        foreach ($this->all() as $key => $field) {
            $raw = isset($posted[$key]) && is_scalar($posted[$key]) ? (string) $posted[$key] : '';

            switch ($field['type']) {
                case 'textarea':
                    $values[$key] = sanitize_textarea_field($raw);
                    break;

                case 'select':
                    $candidate    = sanitize_text_field($raw);
                    $values[$key] = isset($field['options'][$candidate]) ? $candidate : '';
                    break;

                case 'checkbox':
                    $values[$key] = '' !== trim($raw) ? '1' : '';
                    break;

                default:
                    $values[$key] = sanitize_text_field($raw);
                    break;
            }
        }

        return $values;
    }

    /**
     * Validate sanitised answers. Returns errors keyed by field key.
     *
     * @param array<string, string> $values
     * @return array<string, string>
     */
    public function validate(array $values): array
    {
        $errors = [];

        foreach ($this->all() as $key => $field) {
            if (! $field['required']) {
                continue;
            }

            if ('' !== ($values[$key] ?? '')) {
                continue;
            }

            switch ($field['type']) {
                case 'checkbox':
                    /* translators: %s: field label */
                    $message = __('Please confirm "%s".', 'quotlet');
                    break;

                case 'select':
                    /* translators: %s: field label */
                    $message = __('Please choose an option for "%s".', 'quotlet');
                    break;

                default:
                    /* translators: %s: field label */
                    $message = __('Please fill in "%s".', 'quotlet');
                    break;
            }

            $errors[$key] = sprintf($message, $field['label']);
        }

        return $errors;
    }

    /**
     * Build the rows persisted with the quote request.
     *
     * Unanswered fields are skipped. `value` is the machine value add-ons read
     * back; `display` carries the chosen option label for selects so the
     * merchant still sees readable text if the add-on is later deactivated.
     *
     * @param array<string, string> $values
     * @return array<string, array{label: string, type: string, value: string, display: string}>
     */
    public function rows(array $values): array
    {
        $rows = [];

        foreach ($this->all() as $key => $field) {
            $value = (string) ($values[$key] ?? '');

            if ('' === $value) {
                continue;
            }

            $rows[$key] = [
                'label'   => $field['label'],
                'type'    => $field['type'],
                'value'   => $value,
                'display' => 'select' === $field['type'] ? ($field['options'][$value] ?? $value) : '',
            ];
        }

        return $rows;
    }

    /**
     * Render every declared field, in the markup style of the built-in fields.
     *
     * @param array<string, string> $values Current values, keyed by field key.
     * @param array<string, string> $errors Validation errors, keyed by field key.
     */
    public function render(array $values, array $errors): void
    {
        foreach ($this->all() as $key => $field) {
            $inputId = 'estimate-field-' . str_replace('_', '-', $key);
            $name    = self::INPUT_PREFIX . $key;
            $value   = (string) ($values[$key] ?? '');
            $error   = (string) ($errors[$key] ?? '');

            $described = [];

            if ('' !== $field['description']) {
                $described[] = $inputId . '-desc';
            }

            if ('' !== $error) {
                $described[] = $inputId . '-error';
            }

            $aria = '' !== $error ? ' aria-invalid="true"' : '';

            if ([] !== $described) {
                $aria .= ' aria-describedby="' . esc_attr(implode(' ', $described)) . '"';
            }

            // Attribute strings are assembled here so the markup below stays readable.
            $attributes = ($field['required'] ? ' required' : '') . $aria;

            if ('checkbox' === $field['type']) {
                $this->renderCheckbox($field, $inputId, $name, $value, $error, $attributes);
                continue;
            }

            ?>
            <p class="estimate-quote__field">
                <label for="<?php echo esc_attr($inputId); ?>"><?php echo esc_html($field['label']); ?><?php $this->renderRequiredMark($field['required']); ?></label>
                <?php if ('textarea' === $field['type']) : ?>
                    <textarea id="<?php echo esc_attr($inputId); ?>" name="<?php echo esc_attr($name); ?>" rows="5"<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed keywords plus values escaped above. ?>><?php echo esc_textarea($value); ?></textarea>
                <?php elseif ('select' === $field['type']) : ?>
                    <select id="<?php echo esc_attr($inputId); ?>" name="<?php echo esc_attr($name); ?>"<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed keywords plus values escaped above. ?>>
                        <option value=""><?php esc_html_e('Select an option', 'quotlet'); ?></option>
                        <?php foreach ($field['options'] as $optionValue => $optionLabel) : ?>
                            <option value="<?php echo esc_attr($optionValue); ?>"<?php selected($optionValue, $value); ?>><?php echo esc_html($optionLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <input type="text" id="<?php echo esc_attr($inputId); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed keywords plus values escaped above. ?> />
                <?php endif; ?>
                <?php $this->renderHelp($field['description'], $inputId, $error); ?>
            </p>
            <?php
        }
    }

    /**
     * @param array{key: string, label: string, type: string, options: array<string, string>, required: bool, description: string} $field
     */
    private function renderCheckbox(array $field, string $inputId, string $name, string $value, string $error, string $attributes): void
    {
        ?>
        <p class="estimate-quote__field estimate-quote__field--check">
            <span class="estimate-quote__check">
                <input type="checkbox" id="<?php echo esc_attr($inputId); ?>" name="<?php echo esc_attr($name); ?>" value="1"<?php checked('1', $value); ?><?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed keywords plus values escaped above. ?> />
                <label for="<?php echo esc_attr($inputId); ?>"><?php echo esc_html($field['label']); ?><?php $this->renderRequiredMark($field['required']); ?></label>
            </span>
            <?php $this->renderHelp($field['description'], $inputId, $error); ?>
        </p>
        <?php
    }

    private function renderRequiredMark(bool $required): void
    {
        if ($required) {
            echo ' <span class="estimate-quote__req" aria-hidden="true">*</span>';
        }
    }

    private function renderHelp(string $description, string $inputId, string $error): void
    {
        if ('' !== $description) :
            ?>
            <span class="estimate-quote__hint" id="<?php echo esc_attr($inputId . '-desc'); ?>"><?php echo esc_html($description); ?></span>
            <?php
        endif;

        if ('' !== $error) :
            ?>
            <span class="estimate-quote__error" id="<?php echo esc_attr($inputId . '-error'); ?>"><?php echo esc_html($error); ?></span>
            <?php
        endif;
    }
}
