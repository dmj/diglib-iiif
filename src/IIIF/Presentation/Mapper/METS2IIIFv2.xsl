<xsl:transform version="1.0"
               xmlns:json="http://www.w3.org/2005/xpath-functions"
               xmlns:mets="http://www.loc.gov/METS/"
               xmlns:mix="http://www.loc.gov/mix/v20"
               xmlns:xlink="http://www.w3.org/1999/xlink"
               xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output indent="yes"/>

  <xsl:param name="serviceBaseUri"/>
  <xsl:param name="entityType">sc:Manifest</xsl:param>
  <xsl:param name="entityId"/>

  <xsl:variable name="objectId" select="translate(/mets:mets/@OBJID, '/', '_')"/>
  <xsl:variable name="objectBaseUri" select="concat($serviceBaseUri, '/', $objectId)"/>

  <!-- <xsl:param  name="imageComplianceLevel">http://iiif.io/api/image/2/level2.json</xsl:param> -->
  <xsl:param  name="imageComplianceLevel"/>

  <xsl:key name="image-by-id" match="mets:fileGrp[@USE = 'MASTER']/mets:file" use="@ID"/>
  <xsl:key name="techmd-by-id" match="mets:techMD" use="@ID"/>

  <xsl:template match="/">
    <xsl:choose>
      <xsl:when test="$entityType = 'sc:Manifest'">
        <xsl:call-template name="manifest"/>
      </xsl:when>
      <xsl:when test="$entityType = 'sc:Canvas'">
        <xsl:call-template name="canvas"/>
      </xsl:when>
      <xsl:when test="$entityType = 'iiif:Image'">
        <xsl:call-template name="image"/>
      </xsl:when>
      <xsl:when test="$entityType = 'sc:Sequence'">
        <xsl:call-template name="sequence"/>
      </xsl:when>
      <xsl:when test="$entityType = 'oa:Annotation'">
        <xsl:call-template name="annotation"/>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="manifest">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="canvas">
    <xsl:apply-templates select="mets:mets/mets:structMap[@TYPE = 'PHYSICAL']/mets:div[@TYPE = 'physSequence']/mets:div[@ID = $entityId]"/>
  </xsl:template>

  <xsl:template name="annotation">
    <xsl:apply-templates select="mets:mets/mets:structMap[@TYPE = 'PHYSICAL']/mets:div[@TYPE = 'physSequence']/mets:div/mets:fptr[@ID = $entityId]"/>
  </xsl:template>

  <xsl:template name="sequence">
    <xsl:apply-templates select="mets:mets/mets:structMap[@TYPE = 'PHYSICAL']/mets:div[@TYPE = 'physSequence'][@ID = $entityId]"/>
  </xsl:template>

  <xsl:template name="image">
    <xsl:apply-templates select="key('image-by-id', $entityId)" mode="imageAPI"/>
  </xsl:template>

  <xsl:template match="mets:mets">
    <json:map>
      <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/manifest')"/></json:string>
      <json:string key="@type">sc:Manifest</json:string>
      <json:string key="@context">http://iiif.io/api/presentation/2/context.json</json:string>
      <json:string key="label"><xsl:value-of select="@LABEL"/></json:string>
      <!-- TODO: Metadata -->

      <json:array key="sequences">
        <xsl:apply-templates select="mets:structMap[@TYPE = 'PHYSICAL']"/>
      </json:array>

    </json:map>
  </xsl:template>

  <xsl:template match="mets:structMap[@TYPE = 'PHYSICAL']/mets:div[@TYPE = 'physSequence']">
    <json:map>
      <xsl:if test="@ID">
        <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/sequence/', @ID)"/></json:string>
      </xsl:if>
      <json:string key="@type">sc:Sequence</json:string>
      <json:array key="canvases">
        <xsl:apply-templates/>
      </json:array>
    </json:map>
  </xsl:template>

  <xsl:template match="mets:div[@TYPE = 'page']">
    <xsl:variable name="canvasUri" select="concat($objectBaseUri, '/canvas/', @ID)"/>
    <json:map>
      <json:string key="@id"><xsl:value-of select="$canvasUri"/></json:string>
      <json:string key="@type">sc:Canvas</json:string>
      <json:string key="label">
        <xsl:choose>
          <xsl:when test="@ORDERLABEL"><xsl:value-of select="@ORDERLABEL"/></xsl:when>
          <xsl:when test="@ORDER"><xsl:value-of select="@ORDER"/></xsl:when>
          <xsl:otherwise><xsl:value-of select="position()"/></xsl:otherwise>
        </xsl:choose>
      </json:string>

      <xsl:variable name="techmd" select="key('techmd-by-id', key('image-by-id', mets:fptr/@FILEID)/@DMDID)"/>
      <json:number key="height">
        <xsl:value-of select="$techmd//mix:imageWidth"/>
      </json:number>
      <json:number key="width">
        <xsl:value-of select="$techmd//mix:imageHeight"/>
      </json:number>

      <json:array key="images">
        <xsl:apply-templates select="mets:fptr[key('image-by-id', @FILEID)]">
          <xsl:with-param name="canvasUri" select="$canvasUri"/>
        </xsl:apply-templates>
      </json:array>
    </json:map>
  </xsl:template>

  <xsl:template match="mets:fptr">
    <xsl:param name="canvasUri" select="concat($objectBaseUri, '/canvas/', ../@ID)"/>
    <json:map>
      <xsl:if test="@ID">
        <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/annotation/', @ID)"/></json:string>
      </xsl:if>
      <json:string key="@type">oa:Annotation</json:string>
      <json:string key="motivation">sc:painting</json:string>
      <json:string key="on"><xsl:value-of select="$canvasUri"/></json:string>
      <json:map key="resource">
        <xsl:apply-templates select="key('image-by-id', @FILEID)"/>
      </json:map>
    </json:map>
  </xsl:template>

  <xsl:template match="mets:file" mode="imageAPI">
    <json:map>
      <json:string key="@context">http://iiif.io/api/image/2/context.json</json:string>
      <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/image/', @ID)"/></json:string>
      <json:string key="@type">iiif:Image</json:string>
      <json:string key="protocol">http://iiif.io/api/image</json:string>
      <json:array key="profile">
        <json:string><xsl:value-of select="$imageComplianceLevel"/></json:string>
      </json:array>
      <!-- TODO: Width & Height -->

    </json:map>
  </xsl:template>

  <xsl:template match="mets:file">
    <json:string key="@id"><xsl:value-of select="mets:FLocat/@xlink:href"/></json:string>
    <json:string key="@type">dctypes:Image</json:string>
    <json:string key="format"><xsl:value-of select="@MIMETYPE"/></json:string>
    <xsl:if test="$imageComplianceLevel">
      <json:map key="service">
        <json:string key="@context">http://iiif.io/api/image/2/context.json</json:string>
        <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/image/', @ID)"/></json:string>
        <json:string key="profile"><xsl:value-of select="$imageComplianceLevel"/></json:string>
      </json:map>
    </xsl:if>
  </xsl:template>

  <xsl:template match="text()"/>
  <xsl:template match="text()" mode="imageAPI"/>

</xsl:transform>
