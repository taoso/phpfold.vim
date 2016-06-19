<?php
namespace Lvht\Phpfold;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Lvht\MsgpackRpc\Handler;
use Lvht\MsgpackRpc\Server;

class Folder extends NodeVisitorAbstract implements Handler
{
    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var Server
     */
    private $server;

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

        $this->vimFold($this->points);
        $this->points = [];
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\FunctionLike) {
            $cmt = $node->getDocComment();
            if ($cmt) {
                $start_line = $cmt->getLine();
            } else {
                $start_line = $node->getLine();
            }
            $this->points[] = [$start_line, $node->getAttribute('endLine')];
        }

        if ($node instanceof Node\Stmt\Property)
        {
            $cmt = $node->getDocComment();
            if ($cmt) {
                $this->points[] = [$cmt->getLine(), $node->getAttribute('endLine')];
            }
        }

        if ($node instanceof Node\Stmt\Use_) {
            $this->use_points[] = [$node->getLine(), $node->getAttribute('endLine')];
        }
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    public function vimFold($points)
    {
        $this->server->call('vim_call_function', ['phpfold#Fold', [$points]]);
    }
}
