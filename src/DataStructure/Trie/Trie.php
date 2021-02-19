<?php

namespace App\DataStructure\Trie;

use App\DataStructure\TextSearchInterface;
use App\Entity\WordInput;
use Exception;

// What is Trie: https://uploads.toptal.io/blog/image/106/toptal-blog-3_F.png
class Trie implements TextSearchInterface
{
    private Node $rootNode;
    private int $nodesCount = 0;
    private int $endNodesCount = 0;
    
    public function __construct()
    {
        $this->rootNode = new Node('', '');
    }
    
    /**
     * @inheritDoc
     */
    public static function constructFromArray(array $array): static
    {
        $tree = new Trie();
    
        foreach ($array as $element) {
            $tree->addValue($element->getPattern(), $element); // TODO make DS more reusable
        }
        
        return $tree;
    }
    
    /**
     * @inheritDoc
     */
    public static function constructFromDictionary(array $dictionary): static
    {
        $tree = new Trie();
        
        foreach ($dictionary as $key => $value) {
            $tree->addValue($key, $value);
        }
        
        return $tree;
    }
    
    public function __toString()
    {
        return $this->rootNode->__toString();
    }
    
    /**
     * @inheritDoc
     */
    public function findMatches(WordInput $wordInput): array
    {
        $text = $wordInput->getInputWithDots();
        $matches = []; // stores $value from matched endNodes
        $nodesSearched = 0;
        
        // perform search from each character
        for ($i = 0; $i < strlen($text); $i++) {
            $node = $this->rootNode;
            
            $remainingInput = substr($text, $i);
            
            while (true) {
                $char = substr($remainingInput, 0, 1);
                $remainingInput = substr($remainingInput, 1);
    
                $nodesSearched++;
    
                $deeperNode = $node->findDeeperNode($char);
                $node = $deeperNode;
                
                if ($deeperNode === false) {
                    break;
                }
                
                if ($deeperNode->isEndNode()) {
                    $match = clone $deeperNode->getValue();
                    
                    if ($match->isStartPattern()) {
                        $match->setPosition($i);
                    } else {
                        // -1 to compensate for added dot at word start
                        $match->setPosition($i - 1);
                    }
                    //$match->setPosition(max($i - 1, 0));
                    $matches[] = $match;
                }
                
                if (strlen($remainingInput) === 0) {
                    break;
                }
            }
        }
        
        //echo "Nodes searched: $nodesSearched\n";
        return $matches;
    }
    
    /**
     * Create an end node with given $value and all nodes to reach it
     *
     * @param string $key   Search string, i.e. full path towards end node
     * @param object $value Value that will be returned when this pattern matches
     */
    public function addValue(string $key, mixed $value): void
    {
        [$pathChar, $remainingPath] = $this->advancePathStrings($key);
        $node = $this->rootNode;
        
        while (true) {
            $deeperNode = $node->findDeeperNode($pathChar); // ~25% #performance drop cuz of inlining
            
            if ($deeperNode !== false) { // continue search deeper
                $node = $deeperNode;
    
                if (strlen($remainingPath) === 0) {
                    throw new Exception(sprintf('Algorithm error while adding "%s" to the tree', $key));
                }
    
                [$pathChar, $remainingPath] = $this->advancePathStrings($remainingPath);
            } else { // create new node and continue search in it (if not end node)
                $newNode = new Node($pathChar, substr($key, 0, $node->getDepth() + 1));
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
    
    /**
     * Set next values for $pathChar and $remainingPath, i.e.
     * splits $remainingPath 1st char into separate variable
     *
     * ~10% #performance drop comparing with inline usage and code repeat of the
     * same 2 lines
     *
     * @param  string $remainingPath
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
