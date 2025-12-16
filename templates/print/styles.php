<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f0f0f0;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 10mm 10mm 10mm 10mm;
        margin: 10mm auto;
        border: 1px solid #d3d3d3;
        border-radius: 5px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .page::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        height: 80%;
        background-image: url('assets/img/watermark.png');
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
        opacity: 0.08;
        z-index: 0;
        pointer-events: none;
    }

    /* İçeriğin, filigranın üstünde kalmasını sağla */
    .content-wrap,
    .signatures,
    .header {
        position: relative;
        z-index: 1;
    }

    .content-wrap {
        flex: 1;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 11px;
        table-layout: fixed;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: left;
        vertical-align: middle;
        overflow-wrap: break-word;
        word-wrap: break-word;
        font-weight: bold;
    }

    th {
        background-color: #f8f8f8;
        font-weight: bold;
        text-align: center;
    }

    .header {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #000;
    }

    .header h1 {
        margin: 0;
        font-size: 18px;
        text-transform: uppercase;
    }

    .header .meta {
        margin-top: 5px;
        font-size: 12px;
        display: flex;
        justify-content: space-between;
    }

    .barcode-container {
        text-align: center;
        overflow: hidden;
    }

    .barcode-svg {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* İmza Alanları */
    .signatures {
        margin-top: auto;
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #000;
    }

    .sig-block {
        width: 32%;
        text-align: center;
        border: 1px solid #ccc;
        padding: 10px;
        height: 100px;
    }

    .sig-title {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 5px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
        display: block;
    }

    .sig-name {
        font-size: 12px;
        margin-top: 5px;
        font-style: italic;
    }

    .no-print {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .btn {
        padding: 8px 16px;
        cursor: pointer;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
    }

    .btn-print {
        background-color: #004990;
        color: white;
    }

    .btn-close {
        background-color: #dc3545;
        color: white;
        margin-left: 10px;
    }

    @media print {
        @page {
            margin: 0;
            size: A4 portrait;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            background: white;
        }

        .page {
            margin: 0;
            border: none !important;
            width: 100%;
            min-height: 296mm;
            /* 297mm tam sınırda taşma yapabilir, 296mm güvenli */
            box-shadow: none !important;
            padding: 10mm;
            display: flex;
            flex-direction: column;
            background: white;
            box-sizing: border-box;
        }

        .content-wrap {
            flex: 1;
        }

        .signatures {
            margin-top: auto;
            border-top: 1px solid #000;
            padding-top: 5mm;
            page-break-inside: avoid;
        }

        .sig-block {
            border: 1px solid #000 !important;
            height: 100px;
        }

        th,
        td {
            border: 1px solid #000 !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>