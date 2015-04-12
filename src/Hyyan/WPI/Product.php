<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hyyan\WPI;

require_once __DIR__ . '/Product/Taxonomies.php';
require_once __DIR__ . '/Product/Meta.php';
require_once __DIR__ . '/Product/Stock.php';

/**
 * Product
 *
 * Handle product translation
 *
 * @author Hyyan
 */
class Product
{

    /**
     * Construct object
     */
    public function __construct()
    {

        // manage product translation
        add_filter(
                'pll_get_post_types'
                , array($this, 'manageProductTranslation')
        );

        // sync post parent (good for grouped products)
        add_filter('admin_init', array($this, 'syncPostParent'));

        new Product\Taxonomies();
        new Product\Meta();
        new Product\Stock();
    }

    /**
     * Notifty polylang about product custom post
     *
     * @param array $types array of custom post names managed by polylang
     *
     * @return array
     */
    public function manageProductTranslation(array $types)
    {

        $options = get_option('polylang');
        $postTypes = $options['post_types'];
        if (!in_array('product', $postTypes)) {
            $options['post_types'][] = 'product';
            update_option('polylang', $options);
        }

        $types [] = 'product';

        return $types;
    }

    /**
     * Tell polylang to sync the post parent
     */
    public function syncPostParent()
    {
        $options = get_option('polylang');
        $sync = $options['sync'];
        if (!in_array('post_parent', $sync)) {
            $options['sync'][] = 'post_parent';
            update_option('polylang', $options);
        }
    }

}