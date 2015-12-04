Codelife
==============================================

star 的作用是收藏，目的是方便以后查找。  
watch 的作用是关注，目的是等我更新的时候，你可以收到通知。  
fork 的作用是参与，目的是你增加新的内容，然后 Pull Request，我会考虑把你的修改和我原来的内容合并。  

## 参加步骤
* 在 GitHub 上 `fork` 到自己的仓库，然后 `clone` 到本地，并设置用户信息。
```
$ git clone https://github.com/cywxer/codelife.git
$ cd codelife
$ git config user.name "yourname"
$ git config user.email "your email"
```
* 修改代码后提交，并推送到自己的仓库。
```
$ #do some change on the content
$ git commit -am "Fix issue #1: change helo to hello"
$ git push
```
* 在 GitHub 网站上提交 pull request。
* 定期使用项目仓库内容更新自己仓库内容。
```
$ git remote add upstream https://github.com/cywxer/codelife
$ git fetch upstream
$ git checkout master
$ git rebase upstream/master
$ git push -f origin master
```




## 目录

1. Free Book
   * [这里收录了很多免费编程书籍,应有尽有](https://github.com/justjavac/free-programming-books-zh_CN)
   * [诸多编程入门wiki](http://wiki.jikexueyuan.com/)

1. 好玩的
   * [在终端下点对点视频聊天](https://github.com/mofarrell/p2pvc)

1. 工具篇

   * [微信调试利器](http://blog.qqbrowser.cc/)
   * [免费翻墙利器lantern-即安即用](https://github.com/getlantern/lantern)
   
1. 按编程语言分类

   * [Android](android)
   * [Golang](golang)
   * [PHP](php)
   * [Python](python)
   
1. 按技术方向分类
   
   * [机器学习](/machine-learning)

1. 未分类

   * [写的比较好RESTful书籍(英文)](https://github.com/tlhunter/consumer-centric-api-design)
      * [网友翻译的其中部分文章](http://www.cnblogs.com/moonz-wu/p/4211626.html)




