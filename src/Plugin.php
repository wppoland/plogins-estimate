<?php

declare(strict_types=1);

namespace Estimate;

use Estimate\Contract\HasHooks;

defined('ABSPATH') || exit;

final class Plugin
{
    private static ?self $instance = null;

    private Container $container;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();
        (require __DIR__ . '/../config/services.php')($this->container);
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Returns the DI container so add-ons can register their own services.
     */
    public function container(): Container
    {
        return $this->container;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        $this->container->get(Migrator::class)->maybeMigrate();

        /** @var array<class-string<HasHooks>> $hooks */
        $hooks = require __DIR__ . '/../config/hooks.php';
        foreach ($hooks as $id) {
            $service = $this->container->get($id);
            if ($service instanceof HasHooks) {
                $service->registerHooks();
            }
        }

        /**
         * Fires after the plugin has fully booted and all services are registered.
         *
         * Add-ons (e.g. Estimate Pro) hook this to extend the DI container and
         * register their own services once the FREE plugin is ready.
         *
         * @param Plugin $plugin The booted plugin instance.
         */
        do_action('estimate/booted', $this);
    }
}
