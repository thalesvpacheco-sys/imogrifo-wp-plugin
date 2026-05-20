<?php
namespace ImoGrifo\DynamicTags;

if (! defined('ABSPATH')) { exit; }

final class Bootstrap
{
    public static function init(): void
    {
        add_action('elementor/dynamic_tags/register', function($manager){

            // === Requires ===
            require_once IMOGRIFO_PATH . '/includes/elementor/dynamictags/TagMetaCTALabel.php';
            require_once IMOGRIFO_PATH . '/includes/elementor/dynamictags/TagMetaCTAUrl.php';
            require_once IMOGRIFO_PATH . '/includes/elementor/dynamictags/TagPostTerms.php';
            require_once IMOGRIFO_PATH . '/includes/elementor/dynamictags/TagPostInfoCompact.php';
            require_once IMOGRIFO_PATH . '/includes/elementor/dynamictags/TagPostTermsFiltered.php';

            // === Registro no Elementor ===
            $manager->register(new \ImoGrifo\DynamicTags\TagMetaCTALabel());
            $manager->register(new \ImoGrifo\DynamicTags\TagMetaCTAUrl());
            $manager->register(new \ImoGrifo\DynamicTags\TagPostTerms());
            $manager->register(new \ImoGrifo\DynamicTags\TagPostInfoCompact());
            $manager->register(new \ImoGrifo\DynamicTags\TagPostTermsFiltered());
        });
    }
}
