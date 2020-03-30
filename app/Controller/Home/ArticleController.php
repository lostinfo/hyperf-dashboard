<?php

declare(strict_types=1);

namespace App\Controller\Home;

use App\Controller\AbstractController;
use App\Model\Article;

class ArticleController extends AbstractController
{
    public function index()
    {
        return $this->response->json(
            Article::where(['can_show' => true])
                ->select(['id', 'title', 'subtitle', 'poster', 'created_at'])
                ->orderBy('sort', 'asc')
                ->paginate((int)($this->request->input('page_size', 15)))
                ->toArray()
        );
    }

    public function info($id)
    {
        $article = Article::findOrFail($id);

        return $this->response->json($article->toArray());
    }
}
