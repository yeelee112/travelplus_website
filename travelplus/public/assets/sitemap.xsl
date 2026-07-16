<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="sm xhtml"
>
    <xsl:output method="html" encoding="UTF-8" indent="yes" />

    <xsl:template match="/">
        <html lang="vi">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <meta name="robots" content="noindex, follow" />
                <title>Sitemap XML | Travel Plus</title>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        margin: 0;
                        background: #f4f7f9;
                        color: #17212b;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 1.5;
                    }
                    .site-header {
                        border-bottom: 1px solid #dbe5ea;
                        background: #ffffff;
                    }
                    .site-header__inner,
                    main {
                        width: min(1280px, calc(100% - 40px));
                        margin: 0 auto;
                    }
                    .site-header__inner {
                        display: flex;
                        min-height: 74px;
                        align-items: center;
                    }
                    .site-header img {
                        display: block;
                        width: 142px;
                        height: auto;
                    }
                    main { padding: 38px 0 60px; }
                    .page-heading {
                        display: flex;
                        align-items: end;
                        justify-content: space-between;
                        gap: 24px;
                        margin-bottom: 22px;
                    }
                    h1 {
                        margin: 0 0 6px;
                        color: #111827;
                        font-size: 30px;
                        line-height: 1.2;
                    }
                    .page-heading p {
                        margin: 0;
                        color: #64748b;
                    }
                    .url-count {
                        flex: 0 0 auto;
                        padding: 8px 12px;
                        border: 1px solid #b9dceb;
                        border-radius: 6px;
                        background: #eef9fd;
                        color: #087ba9;
                        font-weight: 700;
                    }
                    .table-shell {
                        overflow: hidden;
                        border: 1px solid #d8e3e8;
                        border-radius: 8px;
                        background: #ffffff;
                    }
                    .table-scroll { overflow-x: auto; }
                    table {
                        width: 100%;
                        min-width: 920px;
                        border-collapse: collapse;
                    }
                    th,
                    td {
                        padding: 13px 16px;
                        border-bottom: 1px solid #e7eef2;
                        text-align: left;
                        vertical-align: middle;
                    }
                    th {
                        background: #f7fafb;
                        color: #52616d;
                        font-size: 11px;
                        text-transform: uppercase;
                    }
                    tbody tr:last-child td { border-bottom: 0; }
                    tbody tr:hover { background: #fbfdfe; }
                    .url-cell { width: 58%; }
                    .url-cell a {
                        color: #087fae;
                        font-weight: 600;
                        overflow-wrap: anywhere;
                        text-decoration: none;
                    }
                    .url-cell a:hover { text-decoration: underline; }
                    .muted { color: #70808c; }
                    .priority {
                        display: inline-flex;
                        min-width: 38px;
                        min-height: 26px;
                        align-items: center;
                        justify-content: center;
                        border-radius: 5px;
                        background: #edf5e4;
                        color: #537d21;
                        font-weight: 700;
                    }
                    .languages {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 6px;
                    }
                    .languages a {
                        display: inline-flex;
                        min-width: 30px;
                        min-height: 26px;
                        align-items: center;
                        justify-content: center;
                        border: 1px solid #d6e4ea;
                        border-radius: 5px;
                        color: #40515d;
                        font-size: 11px;
                        font-weight: 700;
                        text-decoration: none;
                        text-transform: uppercase;
                    }
                    .languages a:hover {
                        border-color: #009cde;
                        color: #087fae;
                    }
                    @media (max-width: 640px) {
                        .site-header__inner,
                        main { width: min(100% - 24px, 1280px); }
                        main { padding-top: 28px; }
                        .page-heading {
                            align-items: start;
                            flex-direction: column;
                            gap: 14px;
                        }
                        h1 { font-size: 26px; }
                        table { min-width: 0; }
                        th:not(:first-child),
                        td:not(:first-child) { display: none; }
                        .url-cell { width: auto; }
                    }
                </style>
            </head>
            <body>
                <header class="site-header">
                    <div class="site-header__inner">
                        <img src="/assets/images/logo.svg" alt="Travel Plus" />
                    </div>
                </header>
                <main>
                    <div class="page-heading">
                        <div>
                            <h1>Sitemap XML</h1>
                            <p>Danh sách URL công khai dành cho công cụ tìm kiếm.</p>
                        </div>
                        <div class="url-count">
                            <xsl:value-of select="count(sm:urlset/sm:url)" /> URL
                        </div>
                    </div>
                    <div class="table-shell">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Đường dẫn</th>
                                        <th>Cập nhật</th>
                                        <th>Tần suất</th>
                                        <th>Ưu tiên</th>
                                        <th>Ngôn ngữ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <xsl:for-each select="sm:urlset/sm:url">
                                        <tr>
                                            <td class="url-cell">
                                                <a href="{sm:loc}"><xsl:value-of select="sm:loc" /></a>
                                            </td>
                                            <td class="muted">
                                                <xsl:choose>
                                                    <xsl:when test="sm:lastmod"><xsl:value-of select="sm:lastmod" /></xsl:when>
                                                    <xsl:otherwise>-</xsl:otherwise>
                                                </xsl:choose>
                                            </td>
                                            <td class="muted"><xsl:value-of select="sm:changefreq" /></td>
                                            <td><span class="priority"><xsl:value-of select="sm:priority" /></span></td>
                                            <td>
                                                <div class="languages">
                                                    <xsl:for-each select="xhtml:link[@rel='alternate' and @hreflang!='x-default']">
                                                        <a href="{@href}"><xsl:value-of select="@hreflang" /></a>
                                                    </xsl:for-each>
                                                </div>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
