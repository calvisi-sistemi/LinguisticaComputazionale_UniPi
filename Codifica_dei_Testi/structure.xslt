<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:tei="http://www.tei-c.org/ns/1.0"
    xmlns:xml="http://www.w3.org/XML/1998/namespace"
    exclude-result-prefixes="tei">
    <xsl:output method="html" encoding="UTF-8" />


    <!-- Variabili per semplificare selezioni ripetitive -->
    <xsl:variable name="edition" select="id('edizione_digitale')/tei:edition" />
    <xsl:variable name="sourceDesc" select="tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc" />
    <xsl:variable name="encodingDesc" select="tei:TEI/tei:teiHeader/tei:encodingDesc" />
    <xsl:variable name="abstract" select="tei:TEI/tei:teiHeader/tei:profileDesc/tei:abstract"/>
    <xsl:variable name="unita_misura_pagine" select="$sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions/@unit"/>
    <xsl:variable name="text" select="tei:TEI/tei:text" />
    <xsl:variable name="facsimile" select="tei:TEI/tei:facsimile" />
    <!-- Template generale  -->
    <xsl:template match="/">
        <html>
            <head>
                <link rel="stylesheet" href="style.css" type="text/css"/>
                <title>
                    <xsl:value-of select="$edition/tei:title"/>
                </title>
                <meta charset="utf-8"/>
            </head>
            <body>
                <h1>
                    <xsl:value-of select="$edition/tei:title/tei:title[@type='main']"/>
                </h1>
                <h2>
                    <xsl:value-of select="$edition/tei:title/tei:title[@type='sub']"/>
                </h2>
                <h3>
                    <p>
                        A cura di 
                        <xsl:value-of select="id('nome_curatore')" />
                    </p>
                    <p>
                        Con la supervisione di
                        <xsl:value-of select="id('nome_supervisore')"/>
                    </p>
                </h3>
                <h4>
                    <xsl:value-of select="$edition/tei:edition/tei:date"/>
                </h4>
                <div class="navbar">
                    <a href="#info_biblio">Estremi Bibliografici</a>
                    <a href="#produzione_e_curatela">Produzione e curatela</a>
                    <a href="#trascrizione">Trascrizione</a>
                    <a href="#facsimile">Facsimile</a>
                    <!-- Add more links as needed -->
                </div>
                <xsl:apply-templates select="$sourceDesc"/> 
                <xsl:apply-templates select="/tei:TEI/tei:standOff[@xml:id='Produzione' or @xml:id='Curatela']"/> <!-- Seleziono solo gli stand-off "Produzione" e "Curatela"-->
                <xsl:apply-templates select="$encodingDesc"/>
                <xsl:apply-templates select="$text"/>
                <xsl:apply-templates select="tei:TEI" />
            </body>
        </html>$sourceDesc
    </xsl:template>

    <!-- Testo -->
    <xsl:template match="$text">
        <h4 id="trascrizione">Trascrizione</h4>
        <div class="section" id="frontespizio"> 
            <xsl:value-of select="$text/tei:front"/>
        </div>
        <div class="section" id="pagina_disegno">
            <xsl:for-each select="$text/tei:body/tei:div[@xml:id='pagina_disegno']/*">
                <p><xsl:value-of select="."/></p>
            </xsl:for-each>
        </div>
        <div class="section" id="pagina_poesie">
            <xsl:for-each select="$text/tei:body/tei:div[@xml:id='pagina_poesie']/tei:div[@type='strofa']">
                <div class="section">    
                    <div>
                        <xsl:value-of select="tei:head" />
                    </div>
                    <div class="stanza">
                        <xsl:for-each select="tei:lg/tei:l">
                            <xsl:value-of select="." />
                            <br />
                        </xsl:for-each>
                    </div>
                </div>
            </xsl:for-each>    
        </div>
        <div class="section" id="pagina_prosa">
            <div id="pensiero_in_prosa">
                <xsl:value-of select="$text/tei:body/tei:div[@xml:id='pagina_prosa']/tei:div[@xml:id='pensiero_in_prosa']/tei:head" />
            </div>
            <div>
                <xsl:value-of select="$text/tei:body/tei:div[@xml:id='pagina_prosa']/tei:div[@xml:id='pensiero_in_prosa']/tei:p" />
            </div>
        </div>
    </xsl:template>

    <!-- Facsimile -->
    <xsl:template match="tei:TEI">
        <div class="section">
            <h4 id="facsimile">Facsimili</h4>
            <div class="section flex-container">
                <div class="flex-item">
                    <xsl:for-each select="tei:facsimile">
                        <h5>
                            Facsimile n. <xsl:value-of select="./@n" />
                        </h5>
                        <xsl:for-each select="tei:surface">
                            <img src="{./tei:graphic/@url}" width="30%" style="padding: 20px">
                                <xsl:if test="./tei:graphic[@xml:id = 'disegno']">
                                    <xsl:attribute name="id">
                                        <xsl:text>disegno</xsl:text>
                                    </xsl:attribute>
                                </xsl:if>    
                            </img>
                        </xsl:for-each>
                    </xsl:for-each>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <!-- Estremi bibliografici -->
    <xsl:template match="$sourceDesc">
        <div class="section">
            <h4 id="info_biblio">Estremi bibliografici</h4>
            <div class="section">
                <h5>Informazioni bibliografiche</h5>
                <ul>
                    <li>
                        Titolo: <strong><xsl:value-of select="tei:biblStruct/tei:monogr/tei:title/tei:title[@type='main']"/></strong>
                    </li>

                    <li>
                        Sottotitolo: <strong><xsl:value-of select="tei:biblStruct/tei:monogr/tei:title/tei:title[@type='sub']"/></strong>
                    </li>

                    <li>
                        Autore: <strong><xsl:value-of select="id('nome_autore')"/></strong>
                    </li>
                    
                    <li>
                        Editore: <strong><xsl:value-of select="tei:biblStruct/tei:monogr/tei:imprint/tei:publisher"/></strong>
                    </li>
                    
                    <li>
                        Data di pubblicazione: <strong><xsl:value-of select="tei:biblStruct/tei:monogr/tei:imprint/tei:date"/></strong>
                    </li>
                    
                    <li>
                        Serie: <strong><xsl:value-of select="tei:biblStruct/tei:series/tei:title"/></strong> (Numero: <strong><xsl:value-of select="tei:biblStruct/tei:series/tei:biblScope"/></strong>)
                    </li>
                    <li>
                        Riassiunto: 
                        <p><xsl:apply-templates select="$abstract"/></p>
                    </li>                    
                </ul>
            </div>
            <xsl:apply-templates select="tei:msDesc"/>
        </div>
    </xsl:template>

    <xsl:template match="$sourceDesc/tei:msDesc">
        <xsl:variable name="supportDesc" select="tei:physDesc/tei:objectDesc/tei:supportDesc"></xsl:variable>
        <div class="section" tabindex="0">
            <h5>Descrizione degli esemplari</h5>
            <ul>
                <li>Locazione:
                    <ul>
                        <li>Paese: <strong><xsl:value-of select="tei:msIdentifier/tei:country"/></strong></li>
                        <li>Provincia: 
                            <strong><xsl:value-of select="tei:msIdentifier/tei:region[@type='provincia']"/></strong>
                            <xsl:text> (</xsl:text> 
                            <strong><xsl:value-of select="tei:msIdentifier/tei:region/@key" /></strong>
                            <xsl:text>)</xsl:text>
                        </li>
                        <li>Città: <strong><xsl:value-of select="tei:msIdentifier/tei:settlement"/></strong></li>
                        <li>Collezione: <strong><xsl:value-of select="tei:msIdentifier/tei:collection"/></strong></li>
                    </ul>
                    
                </li>
                <li>
                    Contenuti principali:
                    <ul>
                        <li>Pagina 1 (Frontespizio): <xsl:value-of select="tei:msContents/tei:msItem[@n='1']/tei:note"/></li>
                        <li>Pagina 2: <xsl:value-of select="tei:msContents/tei:msItem[@n='2']/tei:note"/></li>
                        <li>Pagina 3: <xsl:value-of select="tei:msContents/tei:msItem[@n='3']/tei:p"/></li>
                        <li>Pagina 4: <xsl:value-of select="tei:msContents/tei:msItem[@n='4']/tei:note"/><br/>Colophon: <p><xsl:value-of select="tei:msContents/tei:msItem[@n='4']/tei:colophon"/></p></li>
                    </ul>
                </li>
                <li>Materiale: <xsl:value-of select="$supportDesc/tei:support/tei:material"/></li>
                <li>Dimensioni: 
                    <xsl:value-of select="$supportDesc/tei:extent/tei:dimensions/tei:width"/> <xsl:value-of select="$unita_misura_pagine"/> 
                    <xsl:text> x </xsl:text> 
                    <xsl:value-of select="$supportDesc/tei:extent/tei:dimensions/tei:height"/> <xsl:value-of select="$unita_misura_pagine"/></li>
            </ul>
        </div>
    </xsl:template>

    <!-- Template per produzione e curatela -->
    <xsl:template match="tei:standOff">
        <div class="section" id="produzione_e_curatela">
            <xsl:variable name="id_corrente" select="@xml:id"/>
            <h4>
                <xsl:value-of select="$id_corrente" /> <!-- Uso direttamente il valore dell'ID come titolo per la sezione-->
            </h4>
            <div class="section">
                <h6>Persone</h6>
                <xsl:apply-templates select="tei:listPerson/tei:person"/> <!-- Inserisco le persone -->    
            </div>
            <div class="section">
                <h6>Organizzazioni</h6>
                <xsl:apply-templates select="tei:listOrg/tei:org" />    
            </div>
        </div>
    </xsl:template>
    
    <!-- Template per info su codifica e progetto -->
    <xsl:template match="$encodingDesc">
        <div class="section">
            <h4>Codifica e progetto</h4>
            <!-- Elaborazione di projectDesc -->
            <div class="section">
                <h5>Descrizione del progetto</h5>
                <xsl:for-each select="tei:projectDesc/*">
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </div>
            <div class="section">
                <h5>Campionamento</h5>
                <xsl:value-of select="tei:samplingDecl"/>
            </div>
            <div  class="section">
                <h5>Dichiarazioni Editoriali</h5>
                <xsl:for-each select="tei:editorialDecl/*">
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </div>
            <div class="section">
                <h5>Sulla metrica</h5>
                <xsl:apply-templates select="tei:metDecl/*" />
            </div>
        </div>
    </xsl:template>
    <!-- Template per le persone -->
    <xsl:template match="tei:person">
        <p>
            <strong><xsl:value-of select="tei:persName" /></strong>
            <xsl:if test="tei:birth">
                <xsl:text> (</xsl:text>
                <xsl:value-of select="tei:birth/tei:date"/>
                <xsl:if test="tei:death">
                    <xsl:text> - </xsl:text>
                    <xsl:value-of select="tei:death/tei:date"/>
                </xsl:if>
                <xsl:text>), </xsl:text>
                originiario di 
                <!-- Luogo di nascita-->
                <xsl:value-of select="tei:birth/tei:placeName/tei:settlement" /> <!-- Nome città -->
                (<xsl:value-of select="tei:birth/tei:placeName/tei:region[@type='provincia']/@key" />), <!-- Codice della provincia-->

                <!-- Eventuale luogo di morte -->
                <xsl:if test="tei:death">
                    e morto a
                    <xsl:value-of select="tei:death/tei:placeName/tei:settlement" /> <!-- Nome città -->
                    (<xsl:value-of select="tei:death/tei:placeName/tei:region[@type='provincia']/@key" />), <!-- Codice della provincia-->    
                </xsl:if>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="@sex = 'M'">
                    <xsl:if test="tei:death">è stato</xsl:if>
                    <xsl:if test="not(tei:death)">è</xsl:if> 
                    un
                </xsl:when>
                <xsl:when test="@sex = 'F'">
                    <xsl:if test="tei:death">è stata</xsl:if>
                    <xsl:if test="not(tei:death)">è</xsl:if> 
                    una
                </xsl:when>
            </xsl:choose>
            <xsl:for-each select="tei:occupation">
                <xsl:value-of select="."/>
                <xsl:choose>
                    <xsl:when test="position() = last()"><xsl:text> </xsl:text></xsl:when>
                    <xsl:when test="position() = last()-1"> e </xsl:when>
                    <xsl:otherwise>, </xsl:otherwise>
                </xsl:choose>        
            </xsl:for-each>
            <xsl:value-of select="tei:nationality"/>.
            <!-- -->
            <xsl:apply-templates select="tei:note/tei:ref"/>
        </p>
    </xsl:template>

    <!-- Template per le Organizzazioni -->
    <xsl:template match="tei:listOrg/tei:org">
        <p>
            <strong><xsl:value-of select="tei:orgName/tei:name" /></strong>,
            <!-- Elaborazione dell'indirizzo -->
            <xsl:for-each select="tei:orgName/tei:address/node()"> <!-- Seleziono uno alla volta i nodi figli di tei:address -->
                <xsl:value-of select="." /> <!-- Ne stampo il valore -->
                <xsl:if test="position() != last()"> <!-- A meno che non sia l'ultimo degli elementi figli di tei:address -->
                    <xsl:text> </xsl:text> <!-- Stampo uno spazio dopo di questo -->
                </xsl:if>
            </xsl:for-each>
            <br />
            <xsl:value-of select="tei:desc" />
           <xsl:apply-templates select="tei:note/tei:ref" />
        </p>
    </xsl:template>
    
    <!-- Template per le liste -->
    <xsl:template match="tei:list">
        <ul>
            <xsl:for-each select="tei:item">
                <li><xsl:value-of select="."/></li>
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!-- Template generico per i paragrafi -->
    <xsl:template match="tei:p">
        <p><xsl:apply-templates select="node()"/></p> <!-- Dividi in paragrafi concordemente, e applica il template opportuno -->
    </xsl:template>

    <!-- Template per la creazione di una lista di link con le referenze esterne -->
    <xsl:template match="tei:ref"> 
        <a href="{./@target}" > <!-- Il contenuto dell'attributi "href" di ogni link è il contenuto dell'attributi "target" del tag <ref> di data.xml -->
            <xsl:value-of select="."/>
        </a>
        <xsl:text> </xsl:text>
    </xsl:template>
</xsl:stylesheet>
