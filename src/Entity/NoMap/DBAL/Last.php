<?php

// src/Entity/NoMap/DBAL/Last.php

namespace App\Entity\NoMap\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * Last ::= "Last" "(" Subselect ")".
 */
class Last extends FunctionNode
{
    private $expr1;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expr1 = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            ' CAST(%s AS TEXT) ',
            $this->expr1->dispatch($sqlWalker)
        );
    }
}
