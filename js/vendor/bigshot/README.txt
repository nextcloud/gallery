Bigshot
=======

Legal
-----
Please see individual files for specific licenses. The Bigshot 
library itself is licensed under the Apache License version 2.0,
but some runtime dependencies are MIT, MPL or BSD-like.


Dependencies
------------
For 2D zoomable images, Bigshot has no dependencies.

For VR panoramas using WebGL (the bigshot.VRPanorama class), 
Bigshot has a required dependency on Sylvester and glUtils -
MIT and MPL, respectively. For debugging of WebGL rendering
contexts, Bigshot requires the webgl-debug library, which
is released under a BSD-like license (see the Chromium project).


Files
-----

LICENSE.txt
  - The Apache License, version 2.0.

bigshot.jar
  - Java JAR file with the MakeImagePyramid tool.
    Execute: 
        java -jar bigshot.jar
    for help.

bigshot.js
  - Uncompressed JavaScript library

bigshot-compressed.js
  - The Bigshot library, compressed using the YUI Compressor

bigshot-full-compressed.js
  - The Bigshot library, with required WebGL dependencies for 
    VR Panoramas, compressed using the YUI Compressor

bigshot-full-and-optional-compressed.js
  - The Bigshot library, with required and optional WebGL 
    dependencies for VR Panoramas, compressed using the YUI 
    Compressor

bigshot.php
  - PHP script to serve image data from .bigshot archives instead
    of from a folder structure

doc/js/
  - JsDoc documentation for the JavaScript library


Tutorial
--------

Please see:

    http://code.google.com/p/bigshot/wiki/Tutorial

For a nicely formatted tutorial.