<?php

/**
 * Elementor widget: Request a Quote.
 *
 * A thin wrapper around the [estimate_quote] shortcode so the quote list and
 * request form can be placed with the Elementor editor. Kept deliberately
 * minimal (renders the shortcode) so a future migration to Elementor v4 atomic
 * widgets is localised to this class. Loaded only from the
 * `elementor/widgets/register` hook, so the \Elementor\Widget_Base base class is
 * guaranteed to exist here.
 *
 * @package Estimate
 */

declare(strict_types=1);

namespace Estimate\Elementor;

use Elementor\Widget_Base;

defined('ABSPATH') || exit;

/**
 * Request a Quote Elementor widget.
 */
final class QuoteRequestWidget extends Widget_Base
{
    /**
     * Widget machine name (matches the shortcode).
     */
    public function get_name(): string
    {
        return 'estimate_quote';
    }

    /**
     * Widget label shown in the editor.
     */
    public function get_title(): string
    {
        return esc_html__('Request a Quote', 'plogins-estimate');
    }

    /**
     * Editor panel icon.
     */
    public function get_icon(): string
    {
        return 'eicon-form-horizontal';
    }

    /**
     * Editor panel categories.
     *
     * @return string[]
     */
    public function get_categories(): array
    {
        return ['woocommerce-elements', 'general'];
    }

    /**
     * Search keywords in the editor.
     *
     * @return string[]
     */
    public function get_keywords(): array
    {
        return ['estimate', 'quote', 'request', 'rfq', 'b2b', 'woocommerce'];
    }

    /**
     * Register the editor controls.
     *
     * The [estimate_quote] shortcode takes no attributes (it renders the current
     * visitor's quote list and request form), so this is an empty content
     * section: it gives the widget a panel without exposing controls.
     */
    protected function register_controls(): void
    {
        $this->start_controls_section(
            'content',
            ['label' => esc_html__('Request a Quote', 'plogins-estimate')]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget on the front end and in the editor preview.
     */
    protected function render(): void
    {
        echo do_shortcode('[estimate_quote]');
    }
}
