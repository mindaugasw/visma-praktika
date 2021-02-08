<?php
namespace App\DataStructure\Trie;

use Exception;

class Node
{
    /** @var array<Node> */
    private array $children;
    
    /** @var string Single character of this node, used for trie search */
    private string $pathChar;
    
    /** @var string Full path to find this node. Useful for debugging, not used in the algorithm */
    private string $fullPath;
    
    /** @var int Node depth. Root node has 0 depth, 0th char has 1 depth */
    private int $depth;
    
    /** @var object value will be returned if this node is matched and is end node */
    private object $value;
    
    /** @var bool Can this node be matched to return its $value? Note that end nodes can also contain children */
    private bool $isEndNode;
    
    
    public function __construct(string $pathChar, string $fullPath)
    {
        $this->children = [];
        
        $this->pathChar = $pathChar;
        $this->fullPath = $fullPath;
        $this->depth = strlen($fullPath);
        $this->isEndNode = false;
    }
    
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
        
        for ($i = 0, $length = count($this->children); $i < $length; $i++) {
            if ($this->children[$i]->pathChar === $pathChar)
                return $this->children[$i];
        }
        return false;
    }
    
    
    // Getters/setters cause ~10% #performance drop
    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }
    
    public function addChild(Node $childNode): void
    {
        $this->children[] = $childNode;
    }
    
    /**
     * @return string
     */
    public function getPathChar(): string
    {
        return $this->pathChar;
    }
    
    /**
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->fullPath;
    }
    
    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }
    
    /**
     * @return object
     */
    public function getValue(): object
    {
        if (!isset($this->value))
            throw new Exception("Tried getting unset value on node \"".$this->fullPath."\"");
        
        return $this->value;
    }
    
    /**
     * @param object $value
     */
    public function setValue(object $value): void
    {
        $this->value = $value;
    }
    
    /**
     * @return bool
     */
    public function isEndNode(): bool
    {
        return $this->isEndNode;
    }
    
    /**
     * @param bool $isEndNode
     */
    public function setIsEndNode(bool $isEndNode): void
    {
        $this->isEndNode = $isEndNode;
    }
}
