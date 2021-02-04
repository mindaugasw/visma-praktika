<?php
namespace App\Entity\Trie;

// https://www.toptal.com/java/the-trie-a-neglected-data-structure
class Trie
{
    public const SEARCH_BY_CHAR = 1;
    public const SEARCH_BY_WORD = 2;
    
    private Node $rootNode;
    //private array $children;
    
    public function __construct()
    {
        //$this->rootNode = new Node("", null, 0);
        $this->rootNode = new Node("", null, 0, false);
        
    
        
        //$this->children = [];
    }
    
    /**
     * Get array of all matches for given text
     * @param int $searchType Search can be performed by word or by character. Use SEARCH_BY_CHAR|SEARCH_BY_WORD
     */
    public function findMatches(string $input, int $searchType)
    {
        
    }
    
    /**
     * Add a possible return value to the tree
     * @param string $textPath
     * @param object $value
     */
    public function addValue(string $textPath, object $value)
    {
        //$this->rootNode->
        
        $pathChar = substr($textPath, 0, 1);
        //$currentPath = $textPath;
        $nextPath = substr($textPath, 1);
        
        $node = $this->rootNode;
        
        while (true) {
            
            /*if (isset($node->children[$pathChar])) { // continue search in deeper node
                $node = $node->children[$pathChar];
                continue;
            }*/
            // check if node with current path char already exists
            // iterating backwards for better performance, as all patters should be sorted alphabetically
            $continueWhile = false;
            for ($i = count($node->children) - 1; $i >= 0; $i--) {
                if ($node->children[$i]->pathChar === $pathChar) {
                    $node = $node->children[$i];
                    $continueWhile = true;
                    break;
                }
            }
            if ($continueWhile)
                continue;
            
            
            // create new node
            $newNode = new Node($pathChar, null, $node->depth + 1, false);
            $node->children[] = $newNode;
            $node = $newNode;
            
            if (strlen($nextPath) !== 0) {
                $pathChar = substr($nextPath, 0, 1);
                $nextPath = substr($nextPath, 1);
            } else
                break;
        }
        
        return 5;
    }
}
