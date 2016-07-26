<?php
namespace Lvht\Phpfold;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class Folder extends NodeVisitorAbstract
{
    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var Parser
     */
    private $parser;
    private $points = [];
    private $use_points = [];

    public function __construct()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser;
    }

    public function fold($path)
    {
        $this->traverser->addVisitor($this);
        $code = file_get_contents($path);
        $stmts = $this->parser->parse($code);
        $this->traverser->traverse($stmts);

        if ($this->use_points) {
            $this->points[] = [current($this->use_points)[0], end($this->use_points)[1]];
        }

        $points = $this->points;
        $this->points = [];
        return $points;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\Array_) {
            $start_line = $node->getLine();
            $end_line = $node->getAttribute('endLine');
            if ($end_line > $start_line) {
                $this->points[] = [$start_line, $end_line];
            }
        }

        if ($node instanceof Node\Stmt\TryCatch) {
            $stmts = $node->stmts;
            $start_line = current($stmts)->getLine();
            $end_line = end($stmts)->getAttribute('endLine');
            if ($end_line > $start_line) {
                $this->points[] = [$start_line, $end_line];
            }

            $stmts = $node->catches;
            $start_line = current($stmts)->getAttribute('startLine') + 1;
            $end_line = end($stmts)->getAttribute('endLine') - 1;
            if ($end_line > $start_line) {
                $this->points[] = [$start_line, $end_line];
            }

            $stmts = $node->finallyStmts;
            if ($stmts) {
                $start_line = current($stmts)->getAttribute('startLine');
                $end_line = end($stmts)->getAttribute('endLine');
                if ($end_line > $start_line) {
                    $this->points[] = [$start_line, $end_line];
                }
            }
        }

        if ($node instanceof Node\Stmt\Foreach_
            || $node instanceof Node\Stmt\For_
            || $node instanceof Node\Stmt\If_
            || $node instanceof Node\Stmt\ElseIf_
            || $node instanceof Node\Stmt\Else_
        ) {
            $stmts = $node->stmts;
            $start_line = current($stmts)->getLine();
            $end_line = end($stmts)->getAttribute('endLine');
            if ($end_line > $start_line) {
                $this->points[] = [$start_line, $end_line];
            }
        }

        if ($node instanceof Node\FunctionLike) {
            $cmt = $node->getDocComment();
            if ($cmt) {
                $start_line = $cmt->getLine();
            } else {
                $start_line = $node->getLine();
            }
            $end_line = $node->getAttribute('endLine');
            if ($end_line > $start_line) {
                $this->points[] = [$start_line, $end_line];
            }
        }

        if ($node instanceof Node\Stmt\Property) {
            $cmt = $node->getDocComment();
            if ($cmt) {
                $this->points[] = [$cmt->getLine(), $node->getAttribute('endLine')];
            }
        }

        if ($node instanceof Node\Stmt\Use_) {
            $this->use_points[] = [$node->getLine(), $node->getAttribute('endLine')];
        }
    }
}
