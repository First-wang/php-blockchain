<?php


namespace App\Services;


class MerkleTree
{
    /**
     * @var MerkleNode $rootNode
     */
    public $rootNode;

    /**
     * MerkleTree constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $nodes = [];

        // 保证交易是整数个
        if (count($data) % 2 != 0) {
            $data[] = $data[count($data) - 1];
        }

        foreach ($data as $datum) {
            $node = new MerkleNode(null, null, $datum);
            $nodes[] = $node;
        }

        while (1) {
            $newLevel = [];
            for ($j = 0; $j < count($nodes); $j += 2) {
                if (isset($nodes[$j + 1])) {
                    $node = new MerkleNode($nodes[$j], $nodes[$j + 1], '');
                    $newLevel[] = $node;
                } else {
                    $newLevel[] = $nodes[$j];
                }
            }

            $nodes = $newLevel;
            if (count($nodes) == 1) {
                break;
            }
        }
        $this->rootNode = $nodes[0];
    }
}
