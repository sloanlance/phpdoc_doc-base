<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  Common HTML customizations

  $Id: html-common.xsl,v 1.18 2002-08-04 12:54:15 goba Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:include href="common.xsl"/>

<!-- We do not want style="" atts to appear in HTML output -->
<xsl:param name="admon.style" select="''"/>

<!-- We do not want extra head links in HTML -->
<xsl:param name="html.extra.head.links" select="0"/>

<!-- This differs from the default as the ",figure,table,example,equation"
     is missing after the book toc (for comformance with the DSSSL output).
-->
<xsl:param name="generate.toc">
appendix  toc
article   toc
book      toc
chapter   toc
part      toc
preface   toc
qandadiv  toc
qandaset  toc
reference toc
sect1     toc
sect2     toc
sect3     toc
sect4     toc
sect5     toc
section   toc
set       toc
</xsl:param>

<!-- This makes the generation a bit faster (no file name printouts) -->
<xsl:param name="chunk.quietly">1</xsl:param>

<!-- Enclose functions in links, add parenthesis -->
<xsl:template match="function">
  <xsl:choose>
    <xsl:when test="name(parent::*)!='funcdef'">
      <xsl:choose>
        <xsl:when test="ancestor::refentry/refnamediv/refname=translate(current(),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')">
          <xsl:call-template name="inline.boldseq">
            <xsl:with-param name="content">
              <xsl:apply-templates/>
              <xsl:text>()</xsl:text>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <a>
            <xsl:attribute name="href">
              <xsl:call-template name="href.target">
                <xsl:with-param name="object" select="id(concat('function.', translate(string(current()),'_','-')))"/> 
              </xsl:call-template>
            </xsl:attribute>
            <xsl:call-template name="inline.boldseq">
              <xsl:with-param name="content">
                <xsl:apply-templates/>
                <xsl:text>()</xsl:text>
              </xsl:with-param>
            </xsl:call-template>
          </a>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
     <xsl:call-template name="inline.monoseq"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- To overcome precedence issues -->
<xsl:template match="funcdef/function">
  <xsl:choose>
    <xsl:when test="$funcsynopsis.decoration != 0">
      <b class="fsfunc"><xsl:apply-templates/></b>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- Title abbreviations are not used in HTML output,
     only in phpweb left menu generation -->
<xsl:template match="titleabbrev"/>

<!-- Add version information bellow function name -->
<xsl:template match="refnamediv">
  <div class="{name(.)}">
    <xsl:call-template name="anchor"/>
    <xsl:choose>
      <xsl:when test="$refentry.generate.name != 0">
        <h2>
          <xsl:call-template name="gentext">
            <xsl:with-param name="key" select="'RefName'"/>
          </xsl:call-template>
        </h2>
      </xsl:when>
      <xsl:when test="$refentry.generate.title != 0">
        <h2>
          <xsl:choose>
            <xsl:when test="../refmeta/refentrytitle">
              <xsl:apply-templates select="../refmeta/refentrytitle"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates select="refname[1]"/>
            </xsl:otherwise>
          </xsl:choose>
        </h2>
      </xsl:when>
    </xsl:choose>
    <p>(<xsl:value-of select="$version/function[@name=string(current()/refname)]/@from"/>)</p>
    <p>
      <xsl:apply-templates/>
    </p>
  </div>
</xsl:template>

<!-- This is the same as in DocBook XSL, except that we
     preserve the role in programlisting and the like -->
<xsl:template match="programlisting|screen|synopsis">
  <xsl:param name="suppress-numbers" select="'0'"/>
  <xsl:variable name="id">
    <xsl:call-template name="object.id"/>
  </xsl:variable>

  <xsl:call-template name="anchor"/>
  
  <xsl:variable name="preclass">
    <xsl:choose>
      <xsl:when test="./@role">
        <xsl:value-of select="./@role"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="name(.)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="content">
    <xsl:choose>
      <xsl:when test="$suppress-numbers = '0'
                      and @linenumbering = 'numbered'
                      and $use.extensions != '0'
                      and $linenumbering.extension != '0'">
        <xsl:variable name="rtf">
          <xsl:apply-templates/>
        </xsl:variable>
        <pre class="{$preclass}">
          <xsl:call-template name="number.rtf.lines">
            <xsl:with-param name="rtf" select="$rtf"/>
          </xsl:call-template>
        </pre>
      </xsl:when>
      <xsl:otherwise>
        <pre class="{$preclass}">
          <xsl:apply-templates/>
        </pre>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="$shade.verbatim != 0">
      <table xsl:use-attribute-sets="shade.verbatim.style">
        <tr>
          <td>
            <xsl:copy-of select="$content"/>
          </td>
        </tr>
      </table>
    </xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="$content"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- Call title printout code - this was not working in
     the 1.48 DBXML distribution -->
<xsl:template name="refentry.titlepage">
  <div class="titlepage">
    <xsl:call-template name="refentry.title"/>
  </div>
</xsl:template>

<!-- Custom mode for titles for navigation without
     "Chapter 1" and other autogenerated content -->
<xsl:template match="*" mode="phpdoc.object.title">
  <xsl:call-template name="substitute-markup">
    <xsl:with-param name="allow-anchors" select="0"/>
    <xsl:with-param name="template" select="'%t'"/>
  </xsl:call-template>
</xsl:template>

<!-- Rendering of methodsynopsis. The output of this should look like:
     
     int preg_match_all ( string pattern, string subject, array matches [, int flags])
     
     working from a structure like this:
     
     <methodsynopsis>
      <type>int</type><methodname>preg_match_all</methodname>
      <methodparam><type>string</type><parameter>pattern</parameter></methodparam>
      <methodparam><type>string</type><parameter>subject</parameter></methodparam>
      <methodparam><type>array</type><parameter>matches</parameter></methodparam>
      <methodparam choice="opt"><type>int</type><parameter>flags</parameter></methodparam>
     </methodsynopsis>
-->

<!-- Override default [java] methodsynopsis rendering -->
<xsl:template match="methodsynopsis">
  <xsl:apply-templates select="." mode="php"/>
</xsl:template>

<!-- Print out the return type, the method name, then the parameters.
     Close all the optional signs opened and close the prentheses -->
<xsl:template match="methodsynopsis" mode="php">
  <xsl:value-of select="concat(./type/text(), ' ')"/>
  <xsl:value-of select="concat(./methodname/text(), ' ( ')"/>
  <xsl:apply-templates select="./methodparam" mode="php"/>
  <xsl:for-each select="./methodparam[@choice = 'opt']">
    <xsl:text>]</xsl:text>
  </xsl:for-each>
  <xsl:text> )</xsl:text>
</xsl:template>

<!-- Print out optional sign if needed, then a comma if this is
     not the first param, then the type and the parameter name -->
<xsl:template match="methodsynopsis/methodparam" mode="php">
  <xsl:if test="@choice = 'opt'">
    <xsl:text> [</xsl:text>
  </xsl:if>
  <xsl:if test="position() != 1">
    <xsl:text>, </xsl:text>
  </xsl:if>
  <xsl:value-of select="concat(./type/text(), ' ', ./parameter/text())"/>
</xsl:template>

</xsl:stylesheet>

