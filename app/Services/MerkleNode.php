<?php


namespace App\Services;


class MerkleNode
{
    /**
     * @var MerkleNode $left
     */
    public $left;

    /**
     * @var MerkleNode $right;
     */
    public $right;

    /**
     * @var string $data
     */
    public $data;

    public function __construct(?MerkleNode $left, ?MerkleNode $right, string $data)
    {
        if (is_null($left) && is_null($right)) {
            $hash = hash('sha256', $data);
        } else {
            $hash = hash('sha256', $left->data . $right->data);
        }
        $this->data = $hash;

        $this->left = $left;
        $this->right = $right;
    }
}
