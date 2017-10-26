<?php
/**
 * meta service
 * @package site-meta
 * @version 0.0.1
 * @upgrade true
 */

namespace SiteMeta\Service;

class Meta {
    
    protected $meta_props = [
        'article:author'                => 'property',
        'article:modified_time'         => 'property',
        'article:published_time'        => 'property',
        'article:publisher'             => 'property',
        'article:section'               => 'property',
        'article:tag'                   => 'property',
        
        'fb:admins'                     => 'property',
        'fb:app_id'                     => 'property',
        'fb:pages'                      => 'property',
        'fb:profile_id'                 => 'property',
        
        'alexaVerifyID'                 => 'name',
        'msvalidate.01'                 => 'name',
        'google-site-verification'      => 'name',
        'yandex-verification'           => 'name',
        'p:domain_verify'               => 'name',
        
        'description'                   => 'name',
        'keywords'                      => 'name',
        'generator'                     => 'name',
        
        'og:description'                => 'property',
        'og:image'                      => 'property',
        'og:site_name'                  => 'property',
        'og:title'                      => 'property',
        'og:type'                       => 'property',
        'og:url'                        => 'property',
        'og:updated_time'               => 'property',
        
        'profile:username'              => 'property',
        'profile:first_name'            => 'property',
        'profile:last_name'             => 'property',
        
        'twitter:card'                  => 'name',
        'twitter:description'           => 'name',
        'twitter:image:src'             => 'name',
        'twitter:title'                 => 'name',
        'twitter:url'                   => 'name',
        
        'theme-color'                   => 'name',
        
        'viewport'                      => 'name'
    ];
    
    public function foot($obj){
        $dis = \Phun::$dispatcher;
        
        $tx = '';
        
        $facebook_js_tag = $obj->facebook_js_tag ?? false;
        $fb_app_id       = null;
        $instagram_js_tag= $obj->instagram_js_embed ?? false;
        $twitter_js_tag  = $obj->twitter_js_embed ?? false;
        
        if(module_exists('site-param')){
            $fb_app_id = $dis->setting->facebook_app_id;
        
            // facebook js api
            if($dis->setting->facebook_js_tag)
                $facebook_js_tag = true;
            
            // alexa analytics
            if(!is_dev() && $dis->setting->alexa_analytics_account && $dis->setting->alexa_analytics_domain){
                $account = $dis->setting->alexa_analytics_account;
                $domain  = $dis->setting->alexa_analytics_domain;
                
                $tx.= '<script id="alexa-sdk">';
                $tx.=   '_atrk_opts={atrk_acct:"' . $account . '",domain:"' . $domain . '",dynamic: true};';
                $tx.=   '(function(){var as=document.createElement(\'script\');';
                $tx.=   'as.type=\'text/javascript\';';
                $tx.=   'as.async=true;';
                $tx.=   'as.src="https://d31qbv1cthcecs.cloudfront.net/atrk.js";';
                $tx.=   'var s=document.getElementsByTagName(\'script\')[0];';
                $tx.=   's.parentNode.insertBefore(as,s);})();';
                $tx.= '</script>';
                $tx.= '<noscript>';
                $tx.=   '<img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=' . $account . '" style="display:none" height="1" width="1" alt="">';
                $tx.= '</noscript>';
            }
            
            // instagram js embed
            if($dis->setting->instagram_js_embed)
                $instagram_js_tag = true;
            
            // twitter js embed
            if($dis->setting->twitter_js_embed)
                $twitter_js_tag = true;
        }
        
        if($facebook_js_tag){
            $tx.= '<script id="fbjs-sdk">';
            $tx.=   '(function(d,s,id){';
            $tx.=       'var js,fjs=d.getElementsByTagName(s)[0];';
            $tx.=       'if(d.getElementById(id)) return;';
            $tx.=       'js=d.createElement(s);';
            $tx.=       'js.id=id;js.src="//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6&appId=';
            $tx.=       $fb_app_id;
            $tx.=       '";fjs.parentNode.insertBefore(js,fjs);';
            $tx.=   '}(document,\'script\',\'facebook-jssdk\'));';
            $tx.= '</script>';
        }
        
        if($instagram_js_tag)
            $tx.= '<script id="igjs-embed" async defer src="//platform.instagram.com/en_US/embeds.js"></script>';
        
        if($twitter_js_tag)
            $tx.= '<script id="twjs-embed" async defer src="https://platform.twitter.com/widgets.js"></script>';
            
        return $tx;
    }
    
    public function head($obj){
        $dis = \Phun::$dispatcher;
        $metas = $this->parseMeta($obj->meta->_metas);
        $nl = is_dev() ? PHP_EOL : '';
        $with_site_param = module_exists('site-param');

        $tx = '<meta charset="utf-8">' . $nl;
        $tx.= '<meta content="IE=edge" http-equiv="X-UA-Compatible">' . $nl;

        foreach($metas as $name => $value){
            if(!isset($this->meta_props[$name]))
                continue;
            
            $prop = $this->meta_props[$name];
            
            if(is_array($value)){
                foreach($value as $val)
                    $tx.= sprintf('<meta %s="%s" content="%s">%s', $prop, $name, hs($val), $nl);
            }else{
                $tx.= sprintf('<meta %s="%s" content="%s">%s', $prop, $name, hs($value), $nl);
            }
        }
        
        // rss feed
        if(isset($metas['feed'])){
            $title = $metas['title'] ?? false;
            if(!$title)
                $title = $with_site_param ? $dis->setting->frontpage_title : $dis->config->name;
            $tx.= sprintf('<link rel="alternate" href="%s" title="%s" type="application/rss+xml">%s', $metas['feed'], hs($title), $nl);
        }
        
        // hreflang
        $lang = $dis->setting->site_language_location ?? $metas['hreflang'] ?? 'id-id';
        $tx.= sprintf('<link rel="alternate" href="%s" hreflang="%s">%s', $metas['canonical'], $lang, $nl);
        
        // canonical
        if(isset($metas['canonical']))
            $tx.= sprintf('<link rel="canonical" href="%s">%s', $metas['canonical'], $nl);
        
        // logos
        $site = $dis->router->to('siteHome');
        $tx.= sprintf('<link rel="shortcut icon" href="%s">%s',                     $site . 'theme/site/static/logo/48x48.png', $nl);
        $tx.= sprintf('<link rel="apple-touch-icon" href="%s">%s',                  $site . 'theme/site/static/logo/100x100.png', $nl);
        $tx.= sprintf('<link rel="apple-touch-icon" href="%s" sizes="72x72">%s',    $site . 'theme/site/static/logo/72x72.png', $nl);
        $tx.= sprintf('<link rel="apple-touch-icon" href="%s" sizes="114x114">%s',  $site . 'theme/site/static/logo/114x114.png', $nl);
        $tx.= sprintf('<link rel="icon" href="%s" sizes="192x192">%s',              $site . 'theme/site/static/logo/192x192.png', $nl);
        
        // amphtml
        if(isset($metas['amphtml']))
            $tx.= sprintf('<link rel="amphtml" href="%s">%s', $metas['amphtml'], $nl);
        
        // schema.org
        if(isset($obj->meta->_schemas)){
            foreach($obj->meta->_schemas as $schema)
                $tx.= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . $nl;
        }
        
        // title
        $title = $dis->config->name;
        if($with_site_param)
            $title = $dis->setting->site_name;
        if(isset($metas['title']))
            $title = $metas['title'] . ' - ' . $title;
        $tx.= sprintf('<title>%s</title>%s', hs($title), $nl);
        
        // google analytics
        if(!is_dev() && !isset($obj->meta->isamp) && !isset($metas['amphtml']) && $with_site_param && $dis->setting->google_analytics_property){
            $tx.= '<script>';
            $tx.=   '(function(i,s,o,g,r,a,m){';
            $tx.=       'i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){';
            $tx.=           '(i[r].q=i[r].q||[]).push(arguments)';
            $tx.=       '},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)';
            $tx.=   '})(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');';
            if(isset($metas['ga_group']))
                $tx.= 'ga(\'set\', \'contentGroup1\', \'' . $metas['ga_group'] . '\');';
            $tx.=   'ga(\'create\',\'' . $dis->setting->google_analytics_property . '\',\'auto\');';
            $tx.=   'ga(\'send\',\'pageview\');';
            $tx.= '</script>' . $nl;
        }
        
        return $tx;
    }
    
    public function parseMeta($metas){
        $used_metas = [];
        $dis = \Phun::$dispatcher;
        
        if(module_exists('site-param')){
            $front_setting = [
                'viewport'                  => 'theme_responsive',
                'alexaVerifyID'             => 'alexa_site_verification',
                'msvalidate.01'             => 'bing_site_verification',
                'fb:app_id'                 => 'facebook_app_id',
                'fb:pages'                  => 'facebook_page_id',
                'google-site-verification'  => 'google_site_verification',
                'p:domain_verify'           => 'pinterest_site_verification',
                'yandex-verification'       => 'yandex_site_verification',
                'theme-color'               => 'site_theme_color'
            ];
            
            foreach($front_setting as $name => $key){
                $val = $dis->setting->$key;
                if(!$val)
                    continue;
                if($name === 'viewport')
                    $val = 'width=device-width, minimum-scale=1, maximum-scale=1, user-scalable=no';
                $used_metas[$name] = $val;
            }
        }
        
        $used_metas['generator'] = 'Phun';
        
        foreach($metas as $name => $value){
            $used_metas[$name] = $value;
            
            switch($name){
            
            case 'canonical':
            case 'og:url':
            case 'page':
            case 'twitter:url':
            case 'url':
                $used_metas['canonical']   = $used_metas['canonical']   ?? $value;
                $used_metas['og:url']      = $used_metas['og:url']      ?? $value;
                $used_metas['page']        = $used_metas['page']        ?? $value;
                $used_metas['twitter:url'] = $used_metas['twitter:url'] ?? $value;
                $used_metas['url']         = $used_metas['url']         ?? $value;
                break;
            
            case 'updated_time':
            case 'article:modified_time':
            case 'og:updated_time':
                $used_metas['article:modified_time'] = $used_metas['article:modified_time'] ?? $value;
                $used_metas['og:updated_time']       = $used_metas['og:updated_time']       ?? $value;
                break;
            
            case 'published_time':
            case 'article:published_time':
                $used_metas['article:published_time'] = $used_metas['article:published_time'] ?? $value;
                break;
            
            case 'description':
            case 'og:description':
            case 'twitter:description':
                $used_metas['description']         = $used_metas['description']         ?? $value;
                $used_metas['og:description']      = $used_metas['og:description']      ?? $value;
                $used_metas['twitter:description'] = $used_metas['twitter:description'] ?? $value;
                break;
            
            case 'image':
            case 'og:image':
            case 'twitter:image:src':
                $used_metas['image']             = $used_metas['image']             ?? $value;
                $used_metas['og:image']          = $used_metas['og:image']          ?? $value;
                $used_metas['twitter:image:src'] = $used_metas['twitter:image:src'] ?? $value;
                $used_metas['twitter:card']      = 'summary_large_image';
                break;
            
            case 'name':
            case 'og:title':
            case 'title':
            case 'twitter:title':
                $used_metas['name']          = $used_metas['name']          ?? $value;
                $used_metas['og:title']      = $used_metas['og:title']      ?? $value;
                $used_metas['title']         = $used_metas['title']         ?? $value;
                $used_metas['twitter:title'] = $used_metas['twitter:title'] ?? $value;
                break;
            
            case 'og:type':
            case 'type':
                $used_metas['og:type'] = $used_metas['og:type'] ?? $value;
                $used_metas['type']    = $used_metas['type']    ?? $value;
                break;
                
            }
        }
        
        return $used_metas;
    }
}