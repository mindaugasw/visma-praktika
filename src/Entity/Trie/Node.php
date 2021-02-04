<?php
namespace App\Entity\Trie;

class Node
{
    
    public array $children;
    
    public string $pathChar;
    public int $depth;
    public object $value; // Value stored to return on matched path
    public bool $isEndNode;
    
    
    //, string $remainingPath
    public function __construct(string $pathChar, $value, int $depth, $isEndNode)
    {
        $this->children = [];
        
        $this->pathChar = $pathChar;
        if ($value !== null)
            $this->value = $value;
        $this->depth = $depth;
        $this->isEndNode = $isEndNode;
        
        /*$pathLength = strlen($remainingPath);
        $this->depth = $depth;
        
        if ($pathLength > 1) {
            $this->pathChar = substr($textPath, 0, 1);
            $this->children[] = new Node(substr($textPath, 1), $value, $depth+1);
            //$this->value = null;
            $this->isEndNode = false;
            
        } elseif ($pathLength === 1) {
            $this->pathChar = $textPath;
            $this->value = $value;
            $this->isEndNode = true;
            
        } else { // $pathLength === 0
            if ($depth !== 0)
                throw new \Exception("Cannot create node with empty path");
            
            $this->pathChar = "";
            $this->isEndNode = false;
        }*/
    }
    
    /*public static function getRootNode(): Node
    {
        $node = new Node();
        $node->pathChar = "";
        $node->depth = 0;
        $node->isEndNode = false;
        return $node;
    }*/
    
    /*public static function getRegularNode(): Node
    {
        
    }*/
    
    
    public function addValue(string $textPath, object $value)
    {
        
    }
    
    
    public function view()
    {
        $str = str_repeat('  ', $this->depth).$this->pathChar."\n";
        foreach ($this->children as $child) {
            $str .= $child->view();
        }
        return $str;
    }
}
