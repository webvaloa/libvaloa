<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2004,2014,2017 Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 * 2005,2006,2008,2010 Joni Halme <jontsa@amigaone.cc>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

/**
 * Adds XSLT features to XML.
 *
 * Allows creating any kind of (text) output using XML and XSL styles.
 */
namespace Libvaloa\Xml;

use RuntimeException;
use DomDocument;

class Xsl
{
    public $properties = array(
        'enablePhpFunctions' => 1,
    );

    /**
     * Array of XSL filenames to include.
     *
     * @var string
     */
    private $xslfiles = array();

    private $templateDom;
    private $templatePreProcessed = false;

    public function __construct()
    {
    }

    /**
     * Adds XSL file to list of files to include.
     *
     *
     * @param mixed $files   Filename with path or array of files
     * @param bool  $prepend If true, file(s) are put to the top of xsl file stack
     */
    public function includeXSL($files, $prepend = false)
    {
        $files = (array) $files;

        foreach ($files as $file) {
            if (!in_array($file, $this->xslfiles, true)) {
                if ($prepend) {
                    array_unshift($this->xslfiles, $file);
                } else {
                    $this->xslfiles[] = $file;
                }
            }
        }
    }

    /**
     * Preprocess (merge) all templates as a single DOM node.
     * This result can then be fetched with getPreProcessedTemplateDom()
     * for possible modification, and set back to the UI with
     * setPreProcessedTemplateDom().
     *
     * @throws RuntimeException
     */
    public function preProcessTemplate()
    {
        return $this->setPreProcessedTemplateDom(Conversion::stylesToDOM($this->xslfiles));
    }

    /**
     * Return the template as DomDocument.
     *
     * @return DomDocument
     */
    public function getPreProcessedTemplateDom()
    {
        if (!$this->templatePreProcessed) {
            $this->preProcessTemplate();
        }

        return $this->templateDom;
    }

    /**
     * Set a processed template for the UI.
     *
     * @param DomDocument $v
     */
    public function setPreProcessedTemplateDom(DomDocument $v)
    {
        $this->templatePreProcessed = true;
        $this->templateDom = $v;
    }

    /**
     * Creates XSL stylesheet and parses XML+XSL using Xml/Conversion.
     *
     * @todo   Allow changing of encoding
     *
     * @param DomDocument $xmldom XML-data as DomDocument
     *
     * @return string Parsed data as string
     *
     * @uses   DomDocument
     */
    public function parse($xmldom) : string
    {
        if (!$this->templatePreProcessed) {
            $this->preProcessTemplate();
        }

        $conversion = new Conversion($xmldom);
        $styles[0] = $this->getPreProcessedTemplateDom();
        $conversion->addStylesheet($styles);

        return (string) $conversion->toString();
    }

    /**
     * self to string conversion.
     *
     *
     * @return string Parsed data as string
     */
    public function __toString()
    {
        try {
            return $this->parse();
        } catch (Exception $e) {
            return '';
        }
    }
}
