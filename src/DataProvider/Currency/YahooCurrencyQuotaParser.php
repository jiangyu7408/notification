<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 20:15.
 */
namespace DataProvider\Currency;

/**
 * Class YahooCurrencyQuotaParser.
 */
class YahooCurrencyQuotaParser
{
    protected $stack = [];
    protected $path;
    protected $payload = [];
    protected $nodeContentBuffer = [];

    /**
     * @param string $content
     *
     * @return array
     */
    public function parse($content)
    {
        $this->payload = [];
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startXML', 'endXML');
        xml_set_character_data_handler($parser, 'charXML');

        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

        $lines = explode("\n", $content);
        foreach ($lines as $val) {
            if (trim($val) == '') {
                continue;
            }
            $data = $val."\n";
            if (!xml_parse($parser, $data)) {
                throw new \RuntimeException(
                    sprintf(
                        'XML error at line %d column %d',
                        xml_get_current_line_number($parser),
                        xml_get_current_column_number($parser)
                    )
                );
            }
        }

        return $this->payload;
    }

    /**
     * @param resource $parser
     * @param string   $data
     */
    protected function charXML($parser, $data)
    {
        if ($this->path === 'list|resources|resource' && count($this->nodeContentBuffer) > 0) {
            $this->onNode($this->nodeContentBuffer);
            $this->nodeContentBuffer = [];
        }
        if ($this->path === "list|resources|resource|field") {
            $this->nodeContentBuffer[] = $data;
        }
    }

    /**
     * @param resource $parser
     * @param string   $name
     */
    protected function endXML($parser, $name)
    {
        end($this->stack);
        if (key($this->stack) == $name) {
            array_pop($this->stack);
        }
    }

    /**
     * @param resource $parser
     * @param string   $name
     * @param array    $attr
     */
    protected function startXML($parser, $name, $attr)
    {
        $this->stack[$name] = [];
        $path = '';
        $total = count($this->stack) - 1;
        $pos = 0;
        foreach (array_keys($this->stack) as $key) {
            if (count($this->stack) > 1) {
                if ($total == $pos) {
                    $path .= $key;
                } else {
                    $path .= $key.'|';
                }
            } else {
                $path .= $key;
            }
            ++$pos;
        }
        $this->path = $path;
    }

    private function onNode(array $buffer)
    {
        if (strpos($buffer[0], 'USD/') !== 0) {
            return;
        }
        $name = $buffer[0];
        $rate = (float) $buffer[2];
        $this->payload[$name] = $rate;
    }
}
