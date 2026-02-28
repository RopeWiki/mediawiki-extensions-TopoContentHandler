<?php

/**
 * TopoContentHandler
 *
 * Defines the content model for Topo namespace pages. Tells MediaWiki that topo content
 * should be treated as YAML text and creates TopoContent instances when loading pages.
 */

class TopoContentHandler extends \TextContentHandler {
    public function __construct(
        $modelId = 'TOPO_DATA',
        $formats = array( 'application/yaml' )
    ) {
        parent::__construct( $modelId, $formats );
    }

    public function serializeContent( Content $content, $format = null ) {
        return parent::serializeContent( $content, $format );
    }

    public function unserializeContent( $text, $format = null ) {
        return new TopoContent( $text );
    }

    public function makeEmptyContent() {
        return new TopoContent( '' );
    }
}