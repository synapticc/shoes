<?php

// src/Entity/NoMap/Walker/SortableNullsWalker.php

namespace App\Entity\NoMap\Walker;

use Doctrine\ORM\Query\SqlWalker;

class SortableNullsWalker extends SqlWalker
{
    public const NULLS_FIRST = 'NULLS FIRST';
    public const NULLS_LAST = 'NULLS LAST';

    public function walkOrderByClause($orderByClause): string
    {
        $sql = parent::walkOrderByClause($orderByClause);

        $nullFields = $this->getQuery()->getHint('SortableNullsWalker.fields');

        if (is_array($nullFields)) {
            // $platform = $this->getConnection()->getDatabasePlatform()->getName();

            $platform = 'postgresql';
            switch ($platform) {
                case 'mysql':
                    // foreach ($nullFields as $field => $sorting) {
                    //     if (self::NULLS_LAST === $sorting) {
                    //         $sql = preg_replace_callback(
                    //             '/ORDER BY (.+)' . preg_quote($field) . ' (ASC|DESC)/i',
                    //             function ($matches) {
                    //                 $order = ($matches === 'ASC') ? 'DESC' : 'ASC';
                    //                 return 'ORDER BY -' . $matches:inlineRefs{references="&#91;&#123;&quot;type&quot;&#58;&quot;inline_reference&quot;,&quot;start_index&quot;&#58;1454,&quot;end_index&quot;&#58;1457,&quot;number&quot;&#58;1,&quot;url&quot;&#58;&quot;https&#58;//gist.github.com/kornushkin/731ca35ab4db1e16ce804a31c4f11056&quot;,&quot;favicon&quot;&#58;&quot;https&#58;//imgs.search.brave.com/xZ1QeMRUaD4Rb2PRlguFbHNXkQKQJMGrNr6XEBVeFkc/rs&#58;fit&#58;32&#58;32&#58;1&#58;0/g&#58;ce/aHR0cDovL2Zhdmlj/b25zLnNlYXJjaC5i/cmF2ZS5jb20vaWNv/bnMvNzdmZjEzNDE3/NDlmMDc4NDRlMGJl/YTdmYzUxNGNkNDdk/OGE5YmRiOWQ0NGVl/NDU4MzZlNzg3OTYy/M2YzOTZiMS9naXN0/LmdpdGh1Yi5jb20v&quot;,&quot;snippet&quot;&#58;&quot;&#123;\&quot;title\&quot;&#58;\&quot;NULLS&#32;FIRST&#32;/&#32;NULLS&#32;LAST&#32;for&#32;doctrine2&#32;·&#32;GitHub\&quot;,\&quot;caption\&quot;&#58;\&quot;kornushkin&#32;/&#32;SortableNullsWalker\&quot;,\&quot;table\&quot;&#58;&#91;&#91;\&quot;\&quot;,\&quot;expression&#32;instanceof&#32;Query\\\\AST\\\\PathExpression&#32;&amp;amp;&amp;amp;\&quot;&#93;,&#91;\&quot;\&quot;,\&quot;&#36;orderByItem-&amp;gt;expression-&amp;gt;type&#32;==&#32;Query\\\\AST\\\\PathExpression&#58;&#58;TYPE_STATE_FIELD\&quot;&#93;,&#91;\&quot;\&quot;,\&quot;)&#32;&#123;\&quot;&#93;,&#91;\&quot;\&quot;,\&quot;&#36;name&#32;=&#32;&#36;this-&amp;gt;getName(&#36;orderByItem-&amp;gt;expression);\&quot;&#93;,&#91;\&quot;\&quot;,\&quot;&#36;nulls&#32;=&#32;&#36;this-&amp;gt;getRule(&#36;name,&#32;&#36;orderByItem-&amp;g…&quot;&#125;&#93;"} . $matches:inlineRefs{references="&#91;&#123;&quot;type&quot;&#58;&quot;inline_reference&quot;,&quot;start_index&quot;&#58;1468,&quot;end_index&quot;&#58;1471,&quot;number&quot;&#58;2,&quot;url&quot;&#58;&quot;https&#58;//stackoverflow.com/questions/12652034/how-can-i-order-by-null-in-dql&quot;,&quot;favicon&quot;&#58;&quot;https&#58;//imgs.search.brave.com/4WRMec_wn8Q9LO6DI43kkBvIL6wD5TYCXztC9C9kEI0/rs&#58;fit&#58;32&#58;32&#58;1&#58;0/g&#58;ce/aHR0cDovL2Zhdmlj/b25zLnNlYXJjaC5i/cmF2ZS5jb20vaWNv/bnMvNWU3Zjg0ZjA1/YjQ3ZTlkNjQ1ODA1/MjAwODhiNjhjYWU0/OTc4MjM4ZDJlMTBi/ODExYmNiNTkzMjdh/YjM3MGExMS9zdGFj/a292ZXJmbG93LmNv/bS8&quot;,&quot;snippet&quot;&#58;&quot;getQuery()-&amp;gt;getHint('SortableNullsWalker.fields'))&#32;&#123;&#32;if&#32;(is_array(&#36;nullFields))&#32;&#123;&#32;&#36;platform&#32;=&#32;&#36;this-&amp;gt;getConnection()-&amp;gt;getDatabasePlatform()-&amp;gt;getName();&#32;switch&#32;(&#36;platform)&#32;&#123;&#32;case&#32;'mysql'&#58;&#32;//&#32;for&#32;mysql&#32;the&#32;nulls&#32;last&#32;is&#32;represented&#32;with&#32;-&#32;before&#32;the&#32;field&#32;name&#32;foreach&#32;(&#36;nullFields&#32;as&#32;&#36;field&#32;=&amp;gt;&#32;&#36;sorting)&#32;&#123;&#32;/**&#32;*&#32;NULLs&#32;are&#32;considered&#32;lower&#32;than&#32;any&#32;non-NULL&#32;value,&#32;*&#32;except&#32;if&#32;a&#32;–&#32;(minus…&quot;&#125;&#93;"} . ' ' . $order;
                    //             },
                    //             $sql
                    //         );
                    //     }
                    // }
                    break;

                case 'oracle':
                case 'postgresql':
                    foreach ($nullFields as $field => $sorting) {
                        $sql = preg_replace(
                            '/(\.'.preg_quote($field).') (ASC|DESC)?\s*/i',
                            '$1 $2 '.$sorting,
                            $sql
                        );
                    }
                    break;

                default:
                    // Handle other platforms as needed
                    break;
            }
        }

        // dd($sql);
        // return $sql;
        return ' ORDER BY p0_.updated DESC NULLS LAST, p0_.created DESC NULLS LAST';
    }
}
