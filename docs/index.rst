.. FMF documentation master file, created by
   sphinx-quickstart on Sun Oct 20 19:01:30 2013.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to FMF's documentation!
===============================

Ryan will fill this in soon.

.. code-block:: php

    <?php

    $imagine = new Imagine\Gd\Imagine();
    // or
    $imagine = new Imagine\Imagick\Imagine();
    // or
    $imagine = new Imagine\Gmagick\Imagine();

    $size    = new Imagine\Image\Box(40, 40);

    $mode    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;
    // or
    $mode    = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;

    $imagine->open('/path/to/large_image.jpg')
        ->thumbnail($size, $mode)
        ->save('/path/to/thumbnail.png')
    ;

Contents:

.. toctree::
   :maxdepth: 2

   installation/overview


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

