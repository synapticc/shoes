<?php

// src/Entity/NoMap/DBAL/TsrankFunction.php

namespace App\Entity\NoMap\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * TsrankFunction ::= "TSRANK" "(" StringPrimary "," StringPrimary ")".
 */
class TsrankFunction extends FunctionNode
{
    public $fieldName;
    public $queryString;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->fieldName = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->queryString = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return
            'ts_rank('.$this->fieldName->dispatch($sqlWalker).', '.
            ' to_tsquery('.$this->queryString->dispatch($sqlWalker).'))';
    }
}
