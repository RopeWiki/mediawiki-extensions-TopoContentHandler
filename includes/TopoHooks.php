<?php

/**
 * TopoHooks
 *
 * MediaWiki hook handlers for the Topo extension:
 * - onBeforePageDisplay: Loads the (redundant) ext.topo ResourceLoader module
 * - onParserFirstCallInit: Registers the {{#topo:}} parser function for embedding topos in wiki pages
 * - onSkinTemplateNavigation: Customizes page tabs for Topo namespace (adds "Edit Topo" tab)
 * - renderTopoSvg: Renders embedded topos when {{#topo:}} is used in wiki content
 */

use MediaWiki\Revision\SlotRecord;

class TopoHooks {

    public static function onBeforePageDisplay( $out, $skin ) {
        $out->addModules( 'ext.topo' );
        return true;
    }

    // Register "{{#topo:}}" so other pages can embed a topo inline.
    public static function onParserFirstCallInit( $parser ) {
        $parser->setFunctionHook( 'topo', [ self::class, 'renderTopoSvg' ] );
        return true;
    }

    public static function onSkinTemplateNavigation( &$skin, &$links ) {
        $title = $skin->getTitle();

        // Only customize tabs for Topo namespace pages (both existing and non-existent)
        if ( $title->getNamespace() === NS_TOPO ) {
            // Rename existing tabs
            if ( isset( $links['views']['view'] ) ) {
                $links['views']['view']['text'] = 'View Topo';
            }
            if ( isset( $links['views']['edit'] ) ) {
                $links['views']['edit']['text'] = 'Edit Raw';
            }

            // Insert "Edit topo" tab
            $editTopoTab = [
                'class' => false,
                'text' => 'Edit Topo',
                'href' => $title->getLocalURL( 'action=edit-topo' ),
                'primary' => true,
            ];

            $newViews = [];
            $inserted = false;

            // For non-existent pages, insert at the beginning
            if ( !$title->exists() ) {
                $newViews['edit-topo'] = $editTopoTab;
                $inserted = true;
            }

            foreach ( $links['views'] as $key => $tab ) {
                $newViews[$key] = $tab;
                // For existing pages, insert after 'view' tab
                if ( $key === 'view' && !$inserted ) {
                    $newViews['edit-topo'] = $editTopoTab;
                    $inserted = true;
                }
            }

            // If we still haven't inserted it (edge case), add it at the end
            if ( !$inserted ) {
                $newViews['edit-topo'] = $editTopoTab;
            }

            $links['views'] = $newViews;
        }

        return true;
    }

    public static function renderTopoSvg( $parser, $pageName = '' ) {
        // Trim so "{{#topo: Name}}" (with a space) resolves correctly.
        $pageName = trim( $pageName );
        if ( $pageName === '' ) {
            $title = $parser->getTitle();
            $pageName = $title ? $title->getText() : '';
        }

        $topoTitle = Title::newFromText( "Topo:$pageName" );
        if ( !$topoTitle || !$topoTitle->exists() ) {
            return [
                '<span class="error">Topo page not found: Topo:' . htmlspecialchars( $pageName ) . '</span>',
                'isHTML' => true,
            ];
        }

        $page = WikiPage::factory( $topoTitle );
        $content = $page->getContent();
        $rawYaml = $content ? $content->getNativeData() : '';

        // Load viewer scripts/styles once per page (key prevents duplicate injection).
        $parser->getOutput()->addHeadItem(
            '<script src="/topo/deps/js-yaml.min.js"></script>' . "\n" .
            '<script src="/topo/lib/renderer.js"></script>' . "\n" .
            '<script src="/topo/lib/viewer.js"></script>' . "\n" .
            '<link rel="stylesheet" href="/topo/style.css" />',
            'topo-viewer-scripts'
        );
        // raw_yaml is read by viewer.js on DOMContentLoaded.
        // Note: only one topo embed per page is supported — a second {{#topo:}} would
        // reuse the first page's YAML because this head item is keyed.
        $parser->getOutput()->addHeadItem(
            '<script>let raw_yaml = ' . json_encode( $rawYaml ) . ';</script>',
            'topo-raw-yaml'
        );

        // getLocalURL() handles URL-encoding of spaces and special characters.
        $editUrl = htmlspecialchars( $topoTitle->getLocalURL( [ 'action' => 'edit-topo' ] ) );

        $html =
            '<div id="topo-container"></div>' .
            '<div style="font-style:italic;font-size:12px;">' .
                '<a href="' . $editUrl . '">edit topo</a>' .
            '</div>';

        return [ $html, 'isHTML' => true ];
    }
}