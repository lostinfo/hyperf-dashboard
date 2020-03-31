<?php
/**
 * Created by PhpStorm.
 * User: wei gao
 * Email:1225039937@qq.com
 * Date: 2020-03-27
 * Time: 10:01
 */

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\Article;
use App\Request\Admin\ArticleStoreRequest;
use Hyperf\Utils\Str;
use League\Flysystem\Filesystem;

class ArticleController extends AbstractController
{

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function index()
    {
        $request = $this->request;
        return $this->response->json(
            Article::where(function ($query) use ($request) {
                if ($title = $request->input('title')) {
                    $query->where('title', 'like', "%{$title}%");
                }
            })->select(['id', 'poster', 'title', 'subtitle', 'created_at'])
                ->orderBy(
                    $request->input('order_by_column', 'id'),
                    $request->input('order_by_direction', 'desc')
                )->paginate((int)($request->input('page_size', 15)))
                ->toArray()
        );
    }

    public function store(ArticleStoreRequest $request)
    {
        $validated = $request->validated();

        $validated = $this->transferContent($validated);

        if ($id = $request->input('id')) {
            $article = Article::findOrFail($id);
        } else {
            $article = new Article();
        }
        $article->fill($validated);
        $article->save();

        return $this->response;

    }

    public function info($id)
    {
        $article = Article::findOrFail($id);
        return $this->response->json($article->toArray());
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return $this->response;
    }

    protected function transferContent($validated)
    {
        // 防盗链资源转换
        $content       = $validated['content'];
        $markdown      = $validated['markdown'];
        $hosts         = [
            "image2\.135editor\.com",
            "mmbiz\.qpic\.cn",
            "mmbiz\.qlogo\.cn",
            "newcdn\.96weixin\.com",
        ];
        $transfer_urls = [];
        foreach ($hosts as $host) {
            preg_match_all("/[^data-]src=\"(http[s]{0,1}:\/\/{$host}[-A-Za-z0-9\+\&\@\#\/\%\?=~_|!:,.;\s]+)\"|url\([&quot;|\"]*(http[s]{0,1}:\/\/{$host}[-A-Za-z0-9\+\&\@\#\/\%\?=~_|!:,.;\s]+)[&quot;|\"]*\)/m", $content, $matchs);
            foreach ($matchs[1] as $src) {
                $src = trim($src);
                if (!empty($src) && !in_array($src, $transfer_urls)) {
                    array_push($transfer_urls, $src);
                }
            }
            foreach ($matchs[2] as $src) {
                $src = trim($src);
                $src = str_replace("&quot;", "", $src);
                if (!empty($src) && !in_array($src, $transfer_urls)) {
                    array_push($transfer_urls, $src);
                }
            }
        }


        // todo 协程
        $client     = new \GuzzleHttp\Client();
        foreach ($transfer_urls as $transfer_url) {
            $res = $client->get($transfer_url);
            if ($res->getStatusCode() == 200) {
                $content_type = $res->getHeader('content-type')[0];
                $extension    = explode('/', $content_type)[1];
                if ($content_type == 'image/x-icon') $extension = 'ico';
                $file_content = $res->getBody()->getContents();
                $file_name    = Str::random(40);
                $url          = "/article/{$file_name}.{$extension}";
                $this->filesystem->write('/public' . $url, $file_content);
                $content  = str_replace($transfer_url, $url, $content);
                $markdown = str_replace($transfer_url, $url, $markdown);
            }
        }
        $validated['content']  = $content;
        $validated['markdown'] = $markdown;
        return $validated;
    }
}