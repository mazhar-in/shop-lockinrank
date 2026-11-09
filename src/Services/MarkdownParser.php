<?php

namespace App\Services;

class MarkdownParser
{
    /**
     * Parse markdown text into sanitized, semantic HTML.
     */
    public static function render(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $markdown);

        // 1. Extract and protect fenced code blocks
        $codeBlocks = [];
        $text = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n(.*?)```/s', function ($matches) use (&$codeBlocks) {
            $key = '%%CODEBLOCK_' . count($codeBlocks) . '%%';
            $lang = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            $code = htmlspecialchars($matches[2], ENT_NOQUOTES, 'UTF-8');
            $class = !empty($lang) ? ' class="language-' . $lang . '"' : '';
            $codeBlocks[$key] = '<pre class="code-block"><code' . $class . '>' . $code . '</code></pre>';
            return "\n\n" . $key . "\n\n";
        }, $text);

        // 2. Extract and protect Display Math: $$ ... $$
        $mathBlocks = [];
        $text = preg_replace_callback('/\$\$(.*?)\$\$/s', function ($m) use (&$mathBlocks) {
            $key = '%%MATH_DISPLAY_' . count($mathBlocks) . '%%';
            $mathBlocks[$key] = [
                'type' => 'display',
                'html' => '<div class="katex-display-math">$$' . htmlspecialchars($m[1], ENT_NOQUOTES, 'UTF-8') . '$$</div>'
            ];
            return "\n\n" . $key . "\n\n";
        }, $text);

        // 3. Extract and protect Display Math: \[ ... \]
        $text = preg_replace_callback('/\\\\\[(.*?)\\\\\]/s', function ($m) use (&$mathBlocks) {
            $key = '%%MATH_DISPLAY_' . count($mathBlocks) . '%%';
            $mathBlocks[$key] = [
                'type' => 'display',
                'html' => '<div class="katex-display-math">\[' . htmlspecialchars($m[1], ENT_NOQUOTES, 'UTF-8') . '\]</div>'
            ];
            return "\n\n" . $key . "\n\n";
        }, $text);

        // 4. Extract and protect Inline Math: \( ... \)
        $text = preg_replace_callback('/\\\\\((.*?)\\\\\)/s', function ($m) use (&$mathBlocks) {
            $key = '%%MATH_INLINE_' . count($mathBlocks) . '%%';
            $mathBlocks[$key] = [
                'type' => 'inline',
                'html' => '<span class="katex-inline-math">\(' . htmlspecialchars($m[1], ENT_NOQUOTES, 'UTF-8') . '\)</span>'
            ];
            return $key;
        }, $text);

        // 5. Extract and protect Inline Math: $ ... $
        // Pandoc/GitHub math rule: opening $ not followed by whitespace, closing $ not preceded by whitespace or \
        $text = preg_replace_callback('/(?<![\\\\\w])\$(?!\s)(.*?)(?<!\s)(?<!\\\\)\$(?!\d)/s', function ($m) use (&$mathBlocks) {
            if (str_contains($m[1], "\n\n")) {
                return $m[0];
            }
            $key = '%%MATH_INLINE_' . count($mathBlocks) . '%%';
            $mathBlocks[$key] = [
                'type' => 'inline',
                'html' => '<span class="katex-inline-math">$' . htmlspecialchars($m[1], ENT_NOQUOTES, 'UTF-8') . '$</span>'
            ];
            return $key;
        }, $text);

        // Escape raw HTML entities to prevent XSS on remaining content
        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        // Parse tables
        $text = self::parseTables($text);

        // Split into block sections separated by 2 or more newlines
        $blocks = preg_split('/\n{2,}/', $text);
        $outputBlocks = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            // Check if block is a code block placeholder
            if (preg_match('/^%%CODEBLOCK_\d+%%$/', $block)) {
                $outputBlocks[] = $block;
                continue;
            }

            // Check if block is a display math placeholder
            if (preg_match('/^%%MATH_DISPLAY_\d+%%$/', $block)) {
                $outputBlocks[] = $block;
                continue;
            }

            // Check if block is a table (already HTML)
            if (str_starts_with($block, '<div class="table-responsive">')) {
                $outputBlocks[] = $block;
                continue;
            }

            // Headings (# H1 to ###### H6)
            if (preg_match('/^#{1,6}\s+/', $block)) {
                $lines = explode("\n", $block);
                $renderedLines = [];
                $listBuffer = [];
                $flushList = function() use (&$listBuffer, &$renderedLines) {
                    if (!empty($listBuffer)) {
                        $renderedLines[] = '<ul class="markdown-list"><li>' . implode("</li>\n<li>", $listBuffer) . '</li></ul>';
                        $listBuffer = [];
                    }
                };

                foreach ($lines as $line) {
                    if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $lm)) {
                        $flushList();
                        $level = strlen($lm[1]);
                        $hText = self::parseInline($lm[2]);
                        $renderedLines[] = "<h{$level}>{$hText}</h{$level}>";
                    } elseif (preg_match('/^[\*\-]\s+(.+)$/', $line, $lm)) {
                        $listBuffer[] = self::parseInline($lm[1]);
                    } elseif (preg_match('/^\d+\.\s+(.+)$/', $line, $lm)) {
                        $flushList();
                        $renderedLines[] = '<ol class="markdown-list"><li>' . self::parseInline($lm[1]) . '</li></ol>';
                    } else {
                        $flushList();
                        $renderedLines[] = '<p>' . self::parseInline($line) . '</p>';
                    }
                }
                $flushList();
                $outputBlocks[] = implode("\n", $renderedLines);
                continue;
            }

            // Horizontal Rule (--- or ***)
            if (preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $block)) {
                $outputBlocks[] = '<hr class="markdown-divider">';
                continue;
            }

            // Blockquote (> Quote)
            if (str_starts_with($block, '&gt;') || str_starts_with($block, '>')) {
                $quoteLines = explode("\n", $block);
                $cleanLines = [];
                foreach ($quoteLines as $ql) {
                    $ql = preg_replace('/^(&gt;|>)\s?/', '', $ql);
                    $cleanLines[] = self::parseInline($ql);
                }
                $outputBlocks[] = '<blockquote class="markdown-blockquote">' . implode("<br>\n", $cleanLines) . '</blockquote>';
                continue;
            }

            // Unordered List (- item or * item)
            if (preg_match('/^[\*\-]\s+/m', $block)) {
                $listLines = explode("\n", $block);
                $items = [];
                $inList = false;
                foreach ($listLines as $ll) {
                    if (preg_match('/^[\*\-]\s+(.+)$/', $ll, $lm)) {
                        $items[] = '<li>' . self::parseInline($lm[1]) . '</li>';
                        $inList = true;
                    } elseif ($inList && !empty(trim($ll))) {
                        // Append to last list item if continuation line
                        $lastIdx = count($items) - 1;
                        if ($lastIdx >= 0) {
                            $items[$lastIdx] = substr($items[$lastIdx], 0, -5) . '<br>' . self::parseInline($ll) . '</li>';
                        }
                    }
                }
                if (!empty($items)) {
                    $outputBlocks[] = '<ul class="markdown-list">' . implode("\n", $items) . '</ul>';
                    continue;
                }
            }

            // Ordered List (1. item)
            if (preg_match('/^\d+\.\s+/m', $block)) {
                $listLines = explode("\n", $block);
                $items = [];
                foreach ($listLines as $ll) {
                    if (preg_match('/^\d+\.\s+(.+)$/', $ll, $lm)) {
                        $items[] = '<li>' . self::parseInline($lm[1]) . '</li>';
                    }
                }
                if (!empty($items)) {
                    $outputBlocks[] = '<ol class="markdown-list">' . implode("\n", $items) . '</ol>';
                    continue;
                }
            }

            // Standard Paragraph
            $parsedLines = [];
            foreach (explode("\n", $block) as $line) {
                $parsedLines[] = self::parseInline($line);
            }
            $outputBlocks[] = '<p>' . implode("<br>\n", $parsedLines) . '</p>';
        }

        $html = implode("\n\n", $outputBlocks);

        // Restore code blocks
        foreach ($codeBlocks as $placeholder => $codeHtml) {
            $html = str_replace($placeholder, $codeHtml, $html);
        }

        // Restore math blocks
        foreach ($mathBlocks as $placeholder => $data) {
            $html = str_replace($placeholder, $data['html'], $html);
        }

        return $html;
    }

    /**
     * Parse inline markdown styles (bold, italics, strike, code, links, images).
     */
    private static function parseInline(string $text): string
    {
        // Inline code `code`
        $text = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $text);

        // Images ![alt](url)
        $text = preg_replace_callback('/!\[([^\]]*)\]\((https?:\/\/[^\)]+)\)/', function ($m) {
            $alt = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return '<img src="' . $url . '" alt="' . $alt . '" class="markdown-img" loading="lazy">';
        }, $text);

        // Links [text](url)
        $text = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\)]+|mailto:[^\)]+)\)/', function ($m) {
            $label = $m[1];
            $url = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="markdown-link">' . $label . ' <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7em;"></i></a>';
        }, $text);

        // Bold (**text** or __text__)
        $text = preg_replace('/(\*\*|__)(.*?)\1/', '<strong>$2</strong>', $text);

        // Italics (*text* or _text_)
        $text = preg_replace('/(?<![\w\*])(\*|_)(?!\s)(.*?)(?!\s)\1(?![\w\*])/', '<em>$2</em>', $text);

        // Strikethrough (~~text~~)
        $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);

        return $text;
    }

    /**
     * Parse markdown tables (| Header 1 | Header 2 |) into responsive HTML tables.
     */
    private static function parseTables(string $text): string
    {
        $lines = explode("\n", $text);
        $result = [];
        $tableBuffer = [];
        $inTable = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Table row contains pipes
            if (str_starts_with($trimmed, '|') && str_ends_with($trimmed, '|')) {
                $tableBuffer[] = $trimmed;
                $inTable = true;
            } elseif ($inTable && str_contains($trimmed, '|')) {
                $tableBuffer[] = $trimmed;
            } else {
                if ($inTable) {
                    $result[] = self::buildTableHtml($tableBuffer);
                    $tableBuffer = [];
                    $inTable = false;
                }
                $result[] = $line;
            }
        }

        if ($inTable && !empty($tableBuffer)) {
            $result[] = self::buildTableHtml($tableBuffer);
        }

        return implode("\n", $result);
    }

    private static function buildTableHtml(array $rows): string
    {
        if (count($rows) < 2) {
            return implode("\n", $rows);
        }

        $html = '<div class="table-responsive"><table class="markdown-table">';

        // Header Row
        $headerLine = array_shift($rows);
        $headers = array_filter(array_map('trim', explode('|', $headerLine)), fn($v) => $v !== '');
        $html .= '<thead><tr>';
        foreach ($headers as $th) {
            $html .= '<th>' . self::parseInline($th) . '</th>';
        }
        $html .= '</tr></thead>';

        // Check if next row is separator (| --- | --- |)
        if (!empty($rows) && preg_match('/^\|?\s*[:\-\s\|]+\s*\|?$/', $rows[0])) {
            array_shift($rows);
        }

        // Body rows
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $cells = array_filter(array_map('trim', explode('|', $row)), fn($v) => $v !== '');
            if (empty($cells)) continue;
            $html .= '<tr>';
            foreach ($cells as $td) {
                $html .= '<td>' . self::parseInline($td) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        return $html;
    }
}
