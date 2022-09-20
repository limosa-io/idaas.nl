<?php

namespace App\Repository;

use App\AuthChain;
use App\AuthChain\Module\ChainInterface;

class ChainRepository
{
    public function all()
    {
        return AuthChain::all();
    }

    public function get($id)
    {
        return AuthChain::find($id);
    }

    public function exists($from, $to)
    {
        return AuthChain::where(['from' => $from, 'to' => $to])->exists();
    }

    public function add($from, $to, $treePosition)
    {
        $authChain = new AuthChain();
        $authChain->from = $from;
        $authChain->to = $to;
        $authChain->position = $treePosition;

        $authChain->save();

        return $authChain;
    }

    public function delete(ChainInterface $chain)
    {
        if ($chain instanceof AuthChain) {
            $chain->delete();
        }
    }
}
