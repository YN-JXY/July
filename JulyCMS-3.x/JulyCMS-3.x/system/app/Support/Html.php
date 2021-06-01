<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Validator\Constraints\Length;

class Html
{
    /**
     * @var string
     */
    protected $html = '';

    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * 快捷创建
     *
     * @param  string $html HTML 内容
     * @return static
     */
    public static function make($html)
    {
        return new static($html);
    }

    public function getHtml()
    {
        return $this->html;
    }

    /**
     * 提取页面链接
     *
     * @return array
     */
    public function extractPageLinks()
    {
        return $this->extract('/href="(\/[^"]*?(\.html|\/))"/');
    }

    /**
     * 提取图片链接
     *
     * @return array
     */
    public function extractImageLinks()
    {
        return $this->extract('/src="(\/[^"]*?\.(?:jpg|jpeg|gif|png|webp))"/');
    }



    // // 提取视频标题
    public function extractMpTitle($video,$home)
    {
        $index = 0;

        $title = [];
        preg_match_all('/<source (.*?)>(.*?)<\/video>/is', $video,$title, PREG_PATTERN_ORDER);

        $imgs = [];
        $img = [];
        preg_match_all('/poster="(\/[^"]*?\.(?:jpg|jpeg|gif|png|webp))"/', $video,$imgs, PREG_PATTERN_ORDER);
        foreach($imgs[1] as $item){
            $img[$index] = $home.'/'.ltrim($item, '\\/');
            $index++;
        }

        $srcs = [];
        $src = [];
        $index = 0;
        preg_match_all('/src="(\/[^"]*?\.mp4)"/', $video,$srcs,PREG_PATTERN_ORDER);
        foreach($srcs[1] as $item){
            $src[$index] = $home.'/'.ltrim($item, '\\/');
            $index++;
        }


        $videos =  [
            'src' => $src,
            'title'=>$title[2],
            'img'=>$img,
        ];
        return $videos;
    }

    //获取整个视频的标签
    public function extractMpLinks(){
        $matches = [];
        preg_match_all('/<video (.*?)>(.*?)<\/video>/is', $this->html,$matches, PREG_PATTERN_ORDER);
        // Log::info($matches[0]);
        return $matches[0];
    }
    /**
     * 提取 PDF 链接
     *
     * @return array
     */
    public function extractPdfLinks()
    {
        return $this->extract('/href="(\/[^"]*?\.pdf)"/');
    }

    /**
     * 提取指定模式的内容
     *
     * @param  string $pattern 模式
     * @param  int $capture = 1 指定分组
     * @return array
     */
    public function extract($pattern, $capture = 1)
    {
        if (! $this->html) {
            return [];
        }
        preg_match_all($pattern, $this->html, $matches, PREG_PATTERN_ORDER);
        return array_values(array_unique($matches[$capture]));
    }

    public function __toString()
    {
        return $this->html;
    }
}
