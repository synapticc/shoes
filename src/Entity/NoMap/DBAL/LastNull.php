<?php

// src/Entity/NoMap/DBAL/LastNull.php

namespace App\Entity\NoMap\DBAL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "LastNull" "(" StringPrimary ")".
 *
 * @see    www.doctrine-project.org
 */
class LastNull extends FunctionNode
{
    public Node $stringPrimary;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            'LOWER(%s)',
            $sqlWalker->walkSimpleArithmeticExpression($this->stringPrimary),
        );
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->stringPrimary = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
