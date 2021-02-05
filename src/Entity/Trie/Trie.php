<?php
namespace App\Entity\Trie;

// What is Trie: https://uploads.toptal.io/blog/image/106/toptal-blog-3_F.png
class Trie
{
    public Node $rootNode;
    
    private int $totalNodes = 0;
    private int $totalEndNodes = 0;
    
    public function __construct()
    {
        $this->rootNode = new Node("", null, 0, false);
    }
    
    /**
     * Get array of all matches for given text
     * //param int $searchType Search can be performed by word or by character. Use SEARCH_BY_CHAR|SEARCH_BY_WORD
     */
    public function findMatches(string $input)
    {
        $matches = []; // stores $value from matched endNodes
        $nodesTraversed = 0;
        
        // perform search from each character
        for ($i = 0; $i < strlen($input); $i++) {
            $node = $this->rootNode;
            
            $remainingInput = substr($input, $i);
            
            while (true) {
                $char = substr($remainingInput, 0, 1);
                $remainingInput = substr($remainingInput, 1);
    
                $nodesTraversed++;
    
                $deeperNode = $node->findDeeperNode($char);
                $node = $deeperNode;
                
                if ($deeperNode === false)
                    break;
                
                if ($deeperNode->isEndNode) {
                    $match = clone $deeperNode->value;
                    $match->setPosition(max($i - 1, 0)); // -1 to compensate for added dot at word start
                    $matches[] = $match;
                }
                
                if (strlen($remainingInput) === 0)
                    break;
            }
            
        }
        
        echo "Nodes searched: $nodesTraversed\n";
        return $matches;
    }
    
    /**
     * Create an end node with given $value and all nodes to reach it
     * @param string $fullPath Search string, i.e. full path towards end node
     * @param object $value Value that will be returned when this pattern matches
     */
    public function addValue(string $fullPath, object $value)
    {
        [$pathChar, $nextPath] = $this->advancePathStrings($fullPath);
    
        $node = $this->rootNode;
        
        while (true) {
            
            $continueWhile = false;
    
            $deeperNode = $node->findDeeperNode($pathChar);
            if ($deeperNode !== null) { // continue search deeper
                $node = $deeperNode;
    
                if (strlen($nextPath) === 0)
                    throw new \Exception("Algorithm error while adding \"$fullPath\" to the trie");
    
                [$pathChar, $nextPath] = $this->advancePathStrings($nextPath);
                continue;
            } else { // create new node and continue search in it
                
            }
            
            for ($i = count($node->children) - 1; $i >= 0; $i--) {
                if ($node->children[$i]->pathChar === $pathChar) {
                    $node = $node->children[$i];
                    $continueWhile = true;
    
                    if (strlen($nextPath) === 0)
                        throw new \Exception("Algorithm error while adding \"$fullPath\" to the trie");
                    
                    [$pathChar, $nextPath] = $this->advancePathStrings($nextPath);
                    break;
                }
            }
            if ($continueWhile)
                continue;
            
            
            // create new node
            $newNode = new Node($pathChar, null, $node->depth + 1, false);
            $newNode->fullPath = substr($fullPath, 0, $newNode->depth);
            $node->children[] = $newNode;
            $node = $newNode;
            $this->totalNodes++;
            
            if (strlen($nextPath) !== 0) {
                [$pathChar, $nextPath] = $this->advancePathStrings($nextPath);
            } else {
                $newNode->isEndNode = true;
                $newNode->value = $value;
                $this->totalEndNodes++;
                break;
            }
            
        }
        
    }
    
    /**
     * Set next values for $pathChar and $remainingPath, i.e.
     * splits $remainingPath 1st char into separate variable
     *
     * ~10% #performance drop comparing with inline usage and code repeat of the
     * same 2 lines
     * @param string $remainingPath
     * @return array<string> [$pathChar, $remainingPath]
     */
    private function advancePathStrings(string $remainingPath): array
    {
        return [
            substr($remainingPath, 0, 1),   // next $pathChar
            substr($remainingPath, 1),      // next $remainingPath
        ];
    }
}
