<?php

/**
 * TopoEditAction
 *
 * Handles the custom "edit-topo" action for Topo namespace pages (action=edit-topo).
 * Loads the topo editor JavaScript and displays the interactive SVG editor instead of
 * the standard MediaWiki text editor.
 */

class TopoEditAction extends Action {

    public function getName() {
        return 'edit-topo';
    }

    public function show() {
        $output = $this->getOutput();
        $title = $this->getTitle();
        $request = $this->getRequest();

        // Support MediaWiki undo URLs (?undo=X&undoafter=Y).
        // For YAML topo content a 3-way text merge is fragile, so we simply
        // restore the undoafter revision's content into the editor — the user
        // can then save it as a new revision that reverts the unwanted change.
        $undoAfterParam = $request->getInt( 'undoafter' );
        $undoParam      = $request->getInt( 'undo' );

        $text = '';
        if ( $undoParam && $undoAfterParam ) {
            $undoAfterRev = Revision::newFromId( $undoAfterParam );
            if ( $undoAfterRev && $undoAfterRev->getTitle()->equals( $title ) ) {
                $undoContent = $undoAfterRev->getContent();
                $text = $undoContent ? $undoContent->getNativeData() : '';
            }
        }

        if ( $text === '' ) {
            $page = WikiPage::factory( $title );
            $content = $page->getContent();
            $text = $content ? $content->getNativeData() : '';
        }

        $output->setPageTitle( $title );

        $output->addHTML(
            '<script>let raw_yaml = ' . json_encode( $text ) . ';</script>' .
            '<script src="/topo/deps/js-yaml.min.js"></script>' .
            '<script src="/topo/lib/renderer.js"></script>' .
            '<script src="/topo/lib/editor.js"></script>' .
            '<script src="/topo/lib/editor-features.js"></script>' .
            '<script src="/topo/lib/editor-ui.js"></script>' .
            '<script src="/topo/lib/editor-feature-list.js"></script>' .
            '<script src="/topo/lib/editor-io.js"></script>' .
            '<link rel="stylesheet" href="/topo/style.css" />' .
            '<div id="topo-container"></div>'
        );
    }
}
