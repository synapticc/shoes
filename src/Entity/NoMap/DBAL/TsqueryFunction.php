<?php

// src/Entity/NoMap/DBAL/TsqueryFunction.php

namespace App\Entity\NoMap\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * TsqueryFunction ::= "TSQUERY" "(" StringPrimary "," StringPrimary "[, " StringPrimary "])".
 */
class TsqueryFunction extends FunctionNode
{
    public $fieldName;
    public $queryString;
    public $configuration;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->fieldName = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->queryString = $parser->StringPrimary();

        $lexer = $parser->getLexer();
        if ($lexer->isNextToken(TokenType::T_COMMA)) {
            $parser->match(TokenType::T_COMMA);
            $this->configuration = $parser->StringPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        if ($this->configuration) {
            $stmnt = $this->fieldName->dispatch($sqlWalker).' @@ to_tsquery('.$this->configuration->dispatch($sqlWalker).', '.$this->queryString->dispatch($sqlWalker).')';
        } else {
            $stmnt = $this->fieldName->dispatch($sqlWalker).' @@ to_tsquery('.$this->queryString->dispatch($sqlWalker).')';
        }

        return $stmnt;
    }
}
