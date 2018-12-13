<xsl:transform version="1.0"
               xmlns:dct="http://purl.org/dc/terms/"
               xmlns:foaf="http://xmlns.com/foaf/0.1/"
               xmlns:json="http://www.w3.org/2005/xpath-functions"
               xmlns:marcrel="http://id.loc.gov/vocabulary/relators/"
               xmlns:mets="http://www.loc.gov/METS/"
               xmlns:mix="http://www.loc.gov/mix/v20"
               xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
               xmlns:skos="http://www.w3.org/2004/02/skos/core#"
               xmlns:xlink="http://www.w3.org/1999/xlink"
               xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <rdf:RDF>
    <rdf:Description rdf:about="http://id.loc.gov/vocabulary/relators/own">
      <skos:prefLabel xml:lang="de">Besitzende Einrichtung</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Holding Institution</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://id.loc.gov/vocabulary/relators/fnd">
      <skos:prefLabel xml:lang="de">Digitalisierungsförderer</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Digitization Sponsor</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/relation">
      <skos:prefLabel xml:lang="de">Digitalisierungsprojekt</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Digitization Project</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/rightsHolder">
      <skos:prefLabel xml:lang="de">Rechteinhaber</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Rights Holder</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/rights">
      <skos:prefLabel xml:lang="de">Rechte</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Rights</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/license">
      <skos:prefLabel xml:lang="de">Lizenz</skos:prefLabel>
      <skos:prefLabel xml:lang="en">License</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/elements/1.1/identifier">
      <skos:prefLabel xml:lang="de">Signatur</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Shelfmark</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/created">
      <skos:prefLabel xml:lang="de">Datensatz angelegt</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Record Created</skos:prefLabel>
    </rdf:Description>
    <rdf:Description rdf:about="http://purl.org/dc/terms/modified">
      <skos:prefLabel xml:lang="de">Datensatz aktualisiert</skos:prefLabel>
      <skos:prefLabel xml:lang="en">Record Modified</skos:prefLabel>
    </rdf:Description>
  </rdf:RDF>

  <xsl:output indent="yes"/>

  <xsl:param name="serviceBaseUri"/>
  <xsl:param name="entityType">sc:Manifest</xsl:param>
  <xsl:param name="entityId"/>

  <xsl:variable name="objectId" select="translate(/mets:mets/@OBJID, '/', '_')"/>
  <xsl:variable name="objectBaseUri" select="concat($serviceBaseUri, '/', $objectId)"/>

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

  <xsl:template name="manifest-metadata">
    <xsl:variable name="rightsMD" select="mets:amdSec/mets:rightsMD[@ID = /mets:mets/mets:fileSec/mets:fileGrp[@USE = 'MASTER']/@ADMID]/mets:mdWrap/mets:xmlData/rdf:Description"/>

    <xsl:if test="$rightsMD/dct:rightsHolder/dct:Agent | $rightsMD/dct:license/dct:LicenseDocument">
      <json:string key="attribution">
        <xsl:value-of select="normalize-space($rightsMD/dct:rightsHolder/dct:Agent/skos:prefLabel)"/>
        <xsl:if test="$rightsMD/dct:license/dct:LicenseDocument">
          <xsl:value-of select="concat(', ', $rightsMD/dct:license/dct:LicenseDocument/skos:prefLabel)"/>
        </xsl:if>
      </json:string>
    </xsl:if>

    <xsl:if test="$rightsMD/dct:license/dct:LicenseDocument/foaf:homepage">
      <json:string key="license">
        <xsl:value-of select="$rightsMD/dct:license/dct:LicenseDocument/foaf:homepage/@rdf:resource"/>
      </json:string>
    </xsl:if>

    <json:array key="metadata">

      <!-- Holding Institution -->
      <json:map>
        <xsl:call-template name="metadata-label">
          <xsl:with-param name="property">http://id.loc.gov/vocabulary/relators/own</xsl:with-param>
        </xsl:call-template>
        <json:string key="value"><xsl:value-of select="substring-before(@LABEL, ',')"/></json:string>
      </json:map>

      <!-- Shelfmark -->
      <json:map>
        <xsl:call-template name="metadata-label">
          <xsl:with-param name="property">http://purl.org/dc/elements/1.1/identifier</xsl:with-param>
        </xsl:call-template>
        <json:string key="value"><xsl:value-of select="substring-after(@LABEL, ',')"/></json:string>
      </json:map>

      <xsl:if test="$rightsMD">

        <!-- Digitization Project -->
        <xsl:if test="$rightsMD/dct:relation/foaf:Project">
          <json:map>
            <xsl:call-template  name="metadata-label">
              <xsl:with-param name="property">http://purl.org/dc/terms/relation</xsl:with-param>
            </xsl:call-template>
            <json:string key="value"><xsl:value-of select="normalize-space($rightsMD/dct:relation/foaf:Project/skos:prefLabel)"/></json:string>
          </json:map>
        </xsl:if>

        <!-- Digitization Sponsor -->
        <xsl:if test="$rightsMD/marcrel:fnd/dct:Agent">
          <json:map>
            <xsl:call-template  name="metadata-label">
              <xsl:with-param name="property">http://id.loc.gov/vocabulary/relators/fnd</xsl:with-param>
            </xsl:call-template>
            <json:string key="value"><xsl:value-of select="normalize-space($rightsMD/marcrel:fnd/dct:Agent/skos:prefLabel)"/></json:string>
          </json:map>
        </xsl:if>

      </xsl:if>

      <!-- Record created -->
      <xsl:if test="mets:metsHdr/@CREATEDATE">
        <json:map>
          <xsl:call-template name="metadata-label">
            <xsl:with-param name="property">http://purl.org/dc/terms/created</xsl:with-param>
          </xsl:call-template>
          <json:string key="value"><xsl:value-of select="mets:metsHdr/@CREATEDATE"/></json:string>
        </json:map>
      </xsl:if>

      <!-- Record modified -->
      <xsl:if test="mets:metsHdr/@LASTMODDATE">
        <json:map>
          <xsl:call-template name="metadata-label">
            <xsl:with-param name="property">http://purl.org/dc/terms/modified</xsl:with-param>
          </xsl:call-template>
          <json:string key="value"><xsl:value-of select="mets:metsHdr/@LASTMODDATE"/></json:string>
        </json:map>
      </xsl:if>

    </json:array>
  </xsl:template>

  <xsl:template name="metadata-label">
    <xsl:param name="property"/>
    <xsl:choose>
      <xsl:when test="document('')/xsl:transform/rdf:RDF/rdf:Description[@rdf:about = $property]">
        <xsl:choose>
          <xsl:when test="count(document('')/xsl:transform/rdf:RDF/rdf:Description[@rdf:about = $property]/skos:prefLabel) > 1">
            <json:array key="label">
              <xsl:for-each select="document('')/xsl:transform/rdf:RDF/rdf:Description[@rdf:about = $property]/skos:prefLabel">
                <json:map>
                  <json:string key="@language"><xsl:value-of select="@xml:lang"/></json:string>
                  <json:string key="@value"><xsl:value-of select="normalize-space()"/></json:string>
                </json:map>
              </xsl:for-each>
            </json:array>
          </xsl:when>
          <xsl:otherwise>
            <json:string key="label"><xsl:value-of select="normalize-space(document('')/xsl:transform/rdf:RDF/rdf:Description[@rdf:about = $property]/skos:prefLabel)"/></json:string>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <json:string key="label"><xsl:value-of select="$property"/></json:string>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="mets:mets">
    <json:map>
      <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/manifest')"/></json:string>
      <json:string key="@type">sc:Manifest</json:string>
      <json:string key="@context">http://iiif.io/api/presentation/2/context.json</json:string>
      <json:string key="label"><xsl:value-of select="@LABEL"/></json:string>

      <xsl:call-template name="manifest-metadata"/>

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

      <xsl:variable name="techmd" select="key('techmd-by-id', key('image-by-id', mets:fptr/@FILEID)/@ADMID)"/>
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

      <xsl:call-template name="insert-canvas-size"/>
      <json:array key="sizes">
        <json:map>
          <xsl:call-template name="insert-canvas-size"/>
        </json:map>
      </json:array>

    </json:map>
  </xsl:template>

  <xsl:template match="mets:file">
    <json:string key="@id">
      <xsl:choose>
        <xsl:when test="starts-with($imageComplianceLevel, 'http://iiif.io/api/image/2/level')">
          <xsl:value-of select="concat($objectBaseUri, '/image/', @ID, '/full/full/0/default.jpg')"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="mets:FLocat/@xlink:href"/>
        </xsl:otherwise>
      </xsl:choose>
    </json:string>
    <json:string key="@type">dctypes:Image</json:string>
    <json:string key="format">image/jpeg</json:string>
    <xsl:call-template name="insert-canvas-size"/>
    <xsl:if test="$imageComplianceLevel">
      <json:map key="service">
        <json:string key="@context">http://iiif.io/api/image/2/context.json</json:string>
        <json:string key="@id"><xsl:value-of select="concat($objectBaseUri, '/image/', @ID)"/></json:string>
        <json:string key="profile"><xsl:value-of select="$imageComplianceLevel"/></json:string>
      </json:map>
    </xsl:if>
  </xsl:template>

  <xsl:template name="insert-canvas-size">
    <xsl:variable name="techmd" select="key('techmd-by-id', @ADMID)"/>
    <xsl:if test="$techmd">
      <json:number key="width">
        <xsl:value-of select="$techmd//mix:imageWidth"/>
      </json:number>
      <json:number key="height">
        <xsl:value-of select="$techmd//mix:imageHeight"/>
      </json:number>
    </xsl:if>
  </xsl:template>

  <xsl:template match="text()"/>
  <xsl:template match="text()" mode="imageAPI"/>

</xsl:transform>
