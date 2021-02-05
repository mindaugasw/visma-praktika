<?php
namespace App\Entity\Trie;

use Exception;

class Node
{
    /** @var array<Node> */
    public array $children;
    
    /** @var string Single character of this node, used for trie search */
    public string $pathChar;
    
    /** @var string Full path to find this node. Useful for debugging, not used in the algorithm */
    public string $fullPath;
    
    /** @var int Node depth. Root node has 0 depth, 0th char has 1 depth */
    public int $depth;
    
    /** @var object value will be returned if this node is matched and is end node */
    public object $value;
    
    /** @var bool Can this node be matched to return its $value? Note that end nodes can also contain children */
    public bool $isEndNode;
    
    
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
    
    /**
     * Check if there is a child node with given $pathChar
     * @param string $pathChar
     * @return Node|false Found node or false
     * @throws Exception
     */
    public function findDeeperNode(string $pathChar)
    {
        if (empty($pathChar))
            throw new Exception("Can't search for empty string");
            
        // iterating backwards cuz during tree building last node will be always
        // needed, as patterns are sorted alphabetically
        //for ($i = count($this->children) - 1; $i >= 0; $i--) { // Doesn't seem to affect performance
        for ($i = 0, $length = count($this->children); $i < $length; $i++) {
            if ($this->children[$i]->pathChar === $pathChar)
                return $this->children[$i];
        }
        return false;
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
    
    
    /**
     * Can be used to build text representation of the tree
     * @return string
     */
    public function __toString()
    {
        $str = str_repeat('    ', $this->depth - 1)
            .$this->pathChar.($this->isEndNode ? "#" : "")."\n";
        
        foreach ($this->children as $child) {
            $str .= $child->view();
        }
        return $str;
    }
}
