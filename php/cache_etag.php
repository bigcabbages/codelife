<?php
/**
 * php etag实现
 *
 * Etag的工作原理
 * Etag在服务器上生成后，客户端通过If-Match或者说If-None-Match这个条件判断请求来验证资源是否修改。我们常见的是使用If-None-Match.请求一个文件的流程可能如下：
 * 新的请求客户端发起HTTP GET请求一个文件(css ,image,js)；服务器处理请求，返回文件内容和一堆Header(包括Etag,例如”2e681a-6-5d044840″),http头状态码为为200.
 * 同一个用户第二次这个文件的请求客户端在一次发起HTTP GET请求一个文件，注意这个时候客户端同时发送一个If-None-Match头，这个头中会包括上次这个文件的Etag(例如”2e681a- 6-5d044840″),这时服务器判断发送过来的Etag和自己计算出来的Etag，因此If-None-Match为False，不返回200，返 回304，客户端继续使用本地缓存；
 * 注意.服务器又设置了Cache-Control:max-age和Expires时,会同时使用，也就是说在完全匹配If-Modified-Since和If-None-Match即检查完修改时间和Etag之后，服务器才能返回304.
 *
 * Apache下面是在Apache中的Etag的配置
 *
 * 在Apache中设置Etag的支持比较简单，只需要在apache的配置中加入下面的内容就可以了：
 * FileETag MTime Size
 * 注解:FileETag指令配置了当文档是基于一个文件时用以创建ETag(实体标签)应答头的文件的属性(ETag的值用于进行缓冲管理以节约网 络带宽)。ETag的值由文件的inode(索引节点)、大小、最后修改时间决定。FileETag指令可以让您选择(如果您想进行选择)这其中哪些要素 将被使用。主要关键字如下：
 * INode 文件的索引节点(inode)数
 * MTime 文件的最后修改日期及时间
 * Size 文件的字节数
 * All 所有存在的域，等价于：FileETag INode MTime Size
 * None 如果一个文档是基于文件的，则不在应答中包含任何ETag头
 * 在大型多WEB集群时,使用ETag时有问题,所以有人建议使用WEB集群时不要使用ETag,其实很好解决,因为多服务器时,INode不一样,所以不同的服务器生成的ETag不一样,
 * 所以用户有可能重复下载(这时ETag就会不准),明白了上面的原理和设置后,解决方法也很容易,只使用ETag后面 二个参数,MTime和Size就好了.只要ETag的计算没有INode参于计算,就会很准了.
 *
 * Nginx Etag的配置
 * 安装插件git clone git://github.com/mikewest/nginx-static-etags.git
 * 配置nginx
 * vi /etc/nginx/nginx.conf
 * 最好是添加到你虚拟主机的server配置里.
 * location ~ .*\.(gif|jpg|jpeg|png|bmp|ico|rar|css|js|zip|xml|txt|flv|swf|mid|doc|cur|xls|pdf|txt|)$ {
 *  FileETag on;
 *  etag_format "%X%X";
 *  expires 30d;
 * }
 * 
 * Expires 是HTTP 1.0 那个时代的东西了，目前来看，可以不使用了，因为HTTP 1.0 的user agent占有率在 0.1% 以下（我们主要面向的web浏览器均默认使用HTTP 1.1）
 * Cache-control 是 HTTP 1.1 的新特性，也是我们主要做文章使用cache策略的工具
 */
ob_start();
// some page content
// Now save all the content from above into a variable
$PageContent = ob_get_contents();

// And clear the buffer, so the contents will not be submitted to the client (we do that later manually)
ob_end_clean();

// Generate unique Hash-ID by using MD5
$HashID = md5($PageContent);

// Specify the time when the page has been changed. For example this date
// can come from the database or any file. Here we define a fixed date value:
$LastChangeTime = 1144055759;

// Define the proxy or cache expire time
$ExpireTime = 3600; // seconds (= one hour)
                    
// Get request headers:
$headers = apache_request_headers();
// you could also use getallheaders() or $_SERVER
// or HTTP_SERVER_VARS

// Set cache/proxy informations:
header('Cache-Control: max-age=' . $ExpireTime); // must-revalidate
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $ExpireTime) . ' GMT');

// Set last modified (this helps search engines
// and other web tools to determine if a page has
// been updated)
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $LastChangeTime) . ' GMT');

// Send a "ETag" which represents the content
// (this helps browsers to determine if the page
// has been changed or if it can be loaded from
// the cache - this will speed up the page loading)
header('ETag: ' . $HashID);

// The browser "asks" us if the requested page has
// been changed and sends the last modified date he
// has in it's internal cache. So now we can check
// if the submitted time equals our internal time value.
// If yes then the page did not get updated

$PageWasUpdated = ! (isset($headers['If-Modified-Since']) and strtotime($headers['If-Modified-Since']) == $LastChangeTime);
// if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag == $_SERVER['HTTP_IF_NONE_MATCH']) {}

// The second possibility is that the browser sends us
// the last Hash-ID he has. If he does we can determine
// if he has the latest version by comparing both IDs.

$DoIDsMatch = (isset($headers['If-None-Match']) and ereg($HashID, $headers['If-None-Match']));
// $modifiedTime = date('D, d M Y H:i:s', $modifiedTime) . ' GMT';
// if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $modifiedTime == $_SERVER['HTTP_IF_MODIFIED_SINCE']) {}

// Does one of the two ways apply ?
if (! $PageWasUpdated or $DoIDsMatch) {
    
    // Okay, the browser already has the
    // latest version of our page in his
    // cache. So just tell him that
    // the page was not modified and DON'T
    // send the content -> this saves bandwith and
    // speeds up the loading for the visitor
    
    header('HTTP/1.1 304 Not Modified');
    
    // That's all, now close the connection:
    header('Connection: close');
    
    // The magical part:
    // No content here ;-)
    // Just the headers
} else {
    
    // Okay, the browser does not have the
    // latest version or does not have any
    // version cached. So we have to send him
    // the full page.
    
    header('HTTP/1.1 200 OK');
    
    // Tell the browser which size the content
    header('Content-Length: ' . strlen($PageContent));
    
    // Send the full content
    echo $PageContent;
}


