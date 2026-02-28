<?php

/**
 * TopoContent
 *
 * Represents the content of a Topo namespace page. When viewing a Topo: page (action=view),
 * getParserOutput() loads the topo viewer JavaScript and renders the SVG container.
 */

class TopoContent extends \TextContent {
    public function __construct( $text, $model_id = 'TOPO_DATA' ) {
        parent::__construct( $text, $model_id );
    }

    public function getParserOutput( Title $title,
        $revId = null,
        ParserOptions $options = null, $generateHtml = true
    ) {
        $po = new ParserOutput();

        if ( $generateHtml ) {
            $yaml = $this->getNativeData();

            $po->addHeadItem( '<script>let raw_yaml = ' . json_encode( $yaml ) . ';</script>', 'topo-raw-yaml' );
            $po->addHeadItem( '<script src="/topo/deps/js-yaml.min.js"></script>', 'topo-js-yaml' );
            $po->addHeadItem( '<script src="/topo/lib/renderer.js"></script>', 'topo-renderer' );
            $po->addHeadItem( '<script src="/topo/lib/viewer.js"></script>', 'topo-viewer' );
            $po->addHeadItem( '<link rel="stylesheet" href="/topo/style.css" />', 'topo-style' );

            $po->setText( '<div id="topo-container"></div>' );
        }

        return $po;
    }
    public function isEmpty() {
        // Determines whether this content can be considered empty.
        // For Y, we want to check whether there's any CDATA:
        $text = trim( strip_tags( $this->getNativeData() ) );
        return $text === '';
    }
    public function isCountable( $hasLinks = null ) {
        // Determines whether this content should be counted as a "page" for the wiki's statistics.
        // Here, we require it to be not-empty and not a redirect:
        return !$this->isEmpty() && !$this->isRedirect();
    }
    
    public function isValid() {
        return parent::isValid();
    }
    public function getTextForSearchIndex() {
        return strip_tags( $this->getNativeData() );
    }
}