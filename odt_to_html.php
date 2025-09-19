<?php
/**
 * Script per convertire un file ODT in HTML
 */

// Percorso del file ODT
$odtFile = __DIR__ . '/views/MODULO RESO.odt';
$outputFile = __DIR__ . '/views/modulo_reso.html';

// Verifica se il file esiste
if (!file_exists($odtFile)) {
    die("Il file ODT non esiste: $odtFile");
}

// Directory temporanea per l'estrazione
$tempDir = sys_get_temp_dir() . '/odt_extract_' . uniqid();
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Estrai il file ODT (che è essenzialmente un file ZIP)
$zip = new ZipArchive();
if ($zip->open($odtFile) === TRUE) {
    $zip->extractTo($tempDir);
    $zip->close();
    echo "File ODT estratto con successo in $tempDir\n";
} else {
    die("Impossibile estrarre il file ODT");
}

// Leggi il file content.xml che contiene il contenuto principale
$contentXml = $tempDir . '/content.xml';
if (!file_exists($contentXml)) {
    die("File content.xml non trovato nell'archivio ODT");
}

$content = file_get_contents($contentXml);

// Leggi anche il file styles.xml per gli stili
$stylesXml = $tempDir . '/styles.xml';
$styles = '';
if (file_exists($stylesXml)) {
    $styles = file_get_contents($stylesXml);
}

// Funzione per convertire il contenuto XML in HTML
function convertOdtToHtml($content, $styles) {
    // Carica i file XML con SimpleXML
    $contentXml = simplexml_load_string($content);
    $stylesXml = $styles ? simplexml_load_string($styles) : null;
    
    if (!$contentXml) {
        return "Errore nel parsing del file content.xml";
    }
    
    // Registra i namespace
    $contentXml->registerXPathNamespace('office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
    $contentXml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
    $contentXml->registerXPathNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
    
    // Inizia a costruire l'HTML
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modulo Reso</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>';
    
    // Estrai il corpo del documento
    $body = $contentXml->xpath('//office:body/office:text');
    
    if (!$body || empty($body)) {
        return $html . "<p>Nessun contenuto trovato nel documento</p></body></html>";
    }
    
    // Processa i paragrafi
    $paragraphs = $contentXml->xpath('//text:p');
    foreach ($paragraphs as $p) {
        // Controlla se è un titolo
        $styleAttr = (string)$p->attributes('text', true)->{'style-name'};
        if (strpos($styleAttr, 'Heading') !== false || strpos($styleAttr, 'Title') !== false) {
            $level = 1; // Default level
            if (preg_match('/Heading(\d+)/', $styleAttr, $matches)) {
                $level = min(6, max(1, (int)$matches[1]));
            }
            $html .= "<h$level>" . htmlspecialchars((string)$p) . "</h$level>\n";
        } else {
            // Paragrafo normale
            $html .= "<p>" . htmlspecialchars((string)$p) . "</p>\n";
        }
    }
    
    // Processa le tabelle
    $tables = $contentXml->xpath('//table:table');
    foreach ($tables as $table) {
        $html .= "<table>\n";
        
        // Righe della tabella
        $rows = $table->xpath('.//table:table-row');
        foreach ($rows as $row) {
            $html .= "  <tr>\n";
            
            // Celle della riga
            $cells = $row->xpath('.//table:table-cell');
            foreach ($cells as $cell) {
                // Determina se è un'intestazione
                $isHeader = false;
                
                // Contenuto della cella
                $cellContent = '';
                $paragraphs = $cell->xpath('.//text:p');
                foreach ($paragraphs as $p) {
                    $cellContent .= htmlspecialchars((string)$p) . "<br>";
                }
                $cellContent = rtrim($cellContent, "<br>");
                
                // Aggiungi la cella all'HTML
                if ($isHeader) {
                    $html .= "    <th>$cellContent</th>\n";
                } else {
                    $html .= "    <td>$cellContent</td>\n";
                }
            }
            
            $html .= "  </tr>\n";
        }
        
        $html .= "</table>\n";
    }
    
    // Processa le liste
    $lists = $contentXml->xpath('//text:list');
    foreach ($lists as $list) {
        $html .= "<ul>\n";
        
        // Elementi della lista
        $items = $list->xpath('.//text:list-item');
        foreach ($items as $item) {
            $paragraphs = $item->xpath('.//text:p');
            foreach ($paragraphs as $p) {
                $html .= "  <li>" . htmlspecialchars((string)$p) . "</li>\n";
            }
        }
        
        $html .= "</ul>\n";
    }
    
    $html .= '</body>
</html>';
    
    return $html;
}

// Converti il contenuto in HTML
$html = convertOdtToHtml($content, $styles);

// Salva il file HTML
file_put_contents($outputFile, $html);
echo "File HTML creato con successo: $outputFile\n";

// Pulisci la directory temporanea
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}

rrmdir($tempDir);
echo "Directory temporanea rimossa\n";
