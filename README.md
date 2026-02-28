# TopoContentHandler

This is an extension to use the [https://github.com/hcooper/CanyonTopo](CanyonTopo) tool on mediawiki website, (which needs to be installed seperatly).

## What it does

* Adds a `Topo:` namespace for storing YAML topo data as wiki pages.
* Adds a handler to display `Topo:` pages as SVG images using the CanyonTopo library.
* Add a handler to edit `Topo:` pages using the interactive CanyonTopo editor.
* Adds a `{{#topo:}}` parser function for embedding topos in wiki pages.

## Architecture

**The extension does not render topos** — it only loads JavaScript and passes data. The YAML content is injected as JSON (`let raw_yaml = ...`), and the JavaScript assumed to be in `/topo/` handles all rendering.
