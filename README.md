# TopoContentHandler

MediaWiki extension for creating and viewing canyon topo diagrams.

## What it does

Adds a `Topo:` namespace for storing YAML topo data, a visual SVG editor (`action=edit-topo`), a viewer for displaying topos, and a `{{#topo:}}` parser function for embedding topos in wiki pages.

## Architecture

**The extension does not render topos** — it only loads JavaScript and passes data. The YAML content is injected as JSON (`let raw_yaml = ...`), and the JavaScript in `/topo/` handles all rendering.

This keeps the topo code **MediaWiki-agnostic**. The same JavaScript works standalone, embedded in wiki pages, or in other contexts. The PHP extension is just a thin integration layer for MediaWiki.
