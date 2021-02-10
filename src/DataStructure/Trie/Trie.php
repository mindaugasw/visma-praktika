<?php
namespace App\DataStructure\Trie;

// What is Trie: https://uploads.toptal.io/blog/image/106/toptal-blog-3_F.png
class Trie
{
    private Node $rootNode;
    private int $nodesCount = 0;
    private int $endNodesCount = 0;
    
    public function __construct()
    {
        $this->rootNode = new Node('', '');
    }
    
    public function __toString()
    {
        return $this->rootNode->__toString();
    }
    
    /**
     * Get array of all matches for given text
     * @param string $input
     * @return array Array of $value from matched end nodes
     */
    public function findMatches(string $input)
    {
        $matches = []; // stores $value from matched endNodes
        $nodesSearched = 0;
        
        // perform search from each character
        for ($i = 0; $i < strlen($input); $i++) {
            $node = $this->rootNode;
            
            $remainingInput = substr($input, $i);
            
            while (true) {
                $char = substr($remainingInput, 0, 1);
                $remainingInput = substr($remainingInput, 1);
    
                $nodesSearched++;
    
                $deeperNode = $node->findDeeperNode($char);
                $node = $deeperNode;
                
                if ($deeperNode === false)
                    break;
                
                if ($deeperNode->isEndNode()) {
                    $match = clone $deeperNode->getValue();
                    $match->setPosition(max($i - 1, 0)); // -1 to compensate for added dot at word start
                    $matches[] = $match;
                }
                
                if (strlen($remainingInput) === 0)
                    break;
            }
        }
        
        //echo "Nodes searched: $nodesSearched\n";
        return $matches;
    }
    
    /**
     * Create an end node with given $value and all nodes to reach it
     * @param string $fullPath Search string, i.e. full path towards end node
     * @param object $value Value that will be returned when this pattern matches
     */
    public function addValue(string $fullPath, object $value): void
    {
        [$pathChar, $remainingPath] = $this->advancePathStrings($fullPath);
        $node = $this->rootNode;
        
        while (true) {
            $deeperNode = $node->findDeeperNode($pathChar); // ~25% #performance drop cuz of inlining
            
            if ($deeperNode !== false) { // continue search deeper
                $node = $deeperNode;
    
                if (strlen($remainingPath) === 0)
                    throw new \Exception(sprintf('Algorithm error while adding "%s" to the tree', $fullPath));
    
                [$pathChar, $remainingPath] = $this->advancePathStrings($remainingPath);
                
            } else { // create new node and continue search in it (if not end node)
                
                $newNode = new Node($pathChar, substr($fullPath, 0, $node->getDepth() + 1));
                $node->addChild($newNode);
                $node = $newNode;
                $this->nodesCount++; 
    
                if (strlen($remainingPath) !== 0) { // continue search deeper
                    [$pathChar, $remainingPath] = $this->advancePathStrings($remainingPath);
                } else { // is end node
                    $newNode->setIsEndNode(true);
                    $newNode->setValue($value);
                    $this->endNodesCount++;
                    break;
                }
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
    
    
    /**
     * @return int
     */
    public function getNodesCount(): int
    {
        return $this->nodesCount;
    }
    
    /**
     * @return int
     */
    public function getEndNodesCount(): int
    {
        return $this->endNodesCount;
    }
}
