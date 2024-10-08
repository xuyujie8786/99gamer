<?php

/**
 * spin an article using a treasure database : treasure.dat
 */
class wp_auto_spin_spin
{
    public $id;
    public $title;
    public $post;
    public $article; // spinned article
    public $debug = false;

    // coming two variables to be used with the function replaceExecludes
    public $htmlfounds; // not to spin
    public $execludewords; // execluded words from spinning
    public $consequent_protect_tags;
    public $all_protected_tags;

    function __construct($id, $title, $post)
    {
        $this->id = $id;
        $this->title = $title;
        $this->post = $post;

        if (isset($_GET['debug']))
            $this->debug = true;
    }

    /**
     * function spin wrap : this plugin is a wraper for spin that switches between api spin and internal spin
     */
    function spin_wrap()
    {

        // check if spinrewriter active
        $opt = get_option('wp_auto_spin', array());

        if (in_array('OPT_AUTO_SPIN_REWRITER', $opt)) {

            return $this->spin_rewriter();
        } elseif (in_array('OPT_AUTO_SPIN_WORDAI', $opt)) {


            //rewrite or avoid
            $wp_auto_spinner_wordai_method = get_option('wp_auto_spinner_wordai_method', 'rewrite');

            if ($wp_auto_spinner_wordai_method == 'avoid') {
                return $this->spin_wordai_avoid();
            } else {
                //default rewrite method	
                return $this->spin_wordai();
            }
        } elseif (in_array('OPT_AUTO_SPIN_TBS', $opt)) {

            return $this->spin_tbs();
        } elseif (in_array('OPT_AUTO_SPIN_CP', $opt)) {

            return $this->spin_cp();
        } elseif (in_array('OPT_AUTO_SPIN_SC', $opt)) {

            return $this->spin_sc();
        } elseif (in_array('OPT_AUTO_SPIN_CR', $opt)) {

            return $this->spin_cr();
        } elseif (in_array('OPT_AUTO_SPIN_ES', $opt)) {

            return $this->spin_es();
        } elseif (in_array('OPT_AUTO_SPIN_BOT', $opt)) {

            return $this->spin_bot();
        } elseif (in_array('OPT_AUTO_SPIN_QU', $opt)) {

            return $this->spin_quillbot();
        } elseif (in_array('OPT_AUTO_SPIN_RP', $opt)) {

            return $this->spin_rp();
        } elseif (in_array('OPT_AUTO_SPIN_RP', $opt)) {

            return $this->spin_rp();
        } elseif (in_array('OPT_AUTO_SPIN_OPENAI', $opt)) {

            return $this->spin_openai();
        } else {

            return $this->spin();
        }
    }

    /**
     * function spin rewriter : using the spin rewriter api
     */
    function spin_rewriter()
    {
        $spinRewriterDebug = false; // ini

        // chek if username and passowrd found
        $wp_auto_spinner_email = get_option('wp_auto_spinner_email', '');
        $wp_auto_spinner_password = get_option('wp_auto_spinner_password', '');
        $opt = get_option('wp_auto_spin', array());
        $wp_auto_spinner_quality = get_option('wp_auto_spinner_quality', 'medium');
        $wp_auto_spinner_execlude = get_option('wp_auto_spinner_execlude', '');

        // execlude title words
        if (in_array('OPT_AUTO_SPIN_TITLE_EX', $opt)) {
            $extitle = explode(' ', $this->title);

            $wp_auto_spinner_execlude = explode("\n", $wp_auto_spinner_execlude);
            $wp_auto_spinner_execlude = array_filter(array_merge($wp_auto_spinner_execlude, $extitle));
            $wp_auto_spinner_execlude = implode(",", $wp_auto_spinner_execlude);
        } else {

            $wp_auto_spinner_execlude = array_filter(explode("\n", $wp_auto_spinner_execlude));
            $wp_auto_spinner_execlude = implode(",", $wp_auto_spinner_execlude);
        }

        wp_auto_spinner_log_new('Spinning', 'Trying to use spinrewriter api');

        if (trim($wp_auto_spinner_email) != '' && trim($wp_auto_spinner_password) != '') {

            // running a quote call
            require_once("SpinRewriterAPI.php");

            // Authenticate yourself.
            $spinrewriter_api = new SpinRewriterAPI($wp_auto_spinner_email, $wp_auto_spinner_password);

            // Make the actual API request and save response as a native PHP array.
            $api_response = $spinrewriter_api->getQuota();

            // check if response is a valid response i.e is array
            if (isset($api_response['status'])) {

                // check if reponse status is OK or Error
                if ($api_response['status'] == 'OK') {

                    // let's check if quote available
                    if ($api_response['api_requests_available'] > 0) {

                        wp_auto_spinner_log_new('SpinRewriter', 'Quota ' . $api_response['api_requests_available']);

                        $protected_terms = "John, Douglas Adams, then";
                        $spinrewriter_api->setProtectedTerms($wp_auto_spinner_execlude);

                        // (optional) Set whether the One-Click Rewrite process automatically protects Capitalized Words outside the article's title.
                        if (in_array('OPT_AUTO_SPIN_AutoProtectedTerms', $opt)) {
                            $spinrewriter_api->setAutoProtectedTerms(true);
                        } else {
                            $spinrewriter_api->setAutoProtectedTerms(false);
                        }

                        // (optional) Set the confidence level of the One-Click Rewrite process.
                        $spinrewriter_api->setConfidenceLevel($wp_auto_spinner_quality);

                        // (optional) Set whether the One-Click Rewrite process uses nested spinning syntax (multi-level spinning) or not.
                        if (in_array('OPT_AUTO_SPIN_NestedSpintax', $opt)) {
                            $spinrewriter_api->setNestedSpintax(false);
                        } else {
                            $spinrewriter_api->setNestedSpintax(false);
                        }

                        // (optional) Set whether Spin Rewriter rewrites complete sentences on its own.
                        if (in_array('OPT_AUTO_SPIN_AutoSentences', $opt)) {
                            $spinrewriter_api->setAutoSentences(true);
                        } else {
                            $spinrewriter_api->setAutoSentences(false);
                        }

                        // (optional) Set whether Spin Rewriter rewrites entire paragraphs on its own.
                        if (in_array('OPT_AUTO_SPIN_AutoParagraphs', $opt)) {
                            $spinrewriter_api->setAutoParagraphs(false);
                        } else {
                            $spinrewriter_api->setAutoParagraphs(false);
                        }

                        // (optional) Set whether Spin Rewriter writes additional paragraphs on its own.
                        if (in_array('OPT_AUTO_SPIN_AutoNewParagraphs', $opt)) {
                            $spinrewriter_api->setAutoNewParagraphs(false);
                        } else {
                            $spinrewriter_api->setAutoNewParagraphs(false);
                        }

                        // (optional) Set whether Spin Rewriter changes the entire structure of phrases and sentences.
                        if (in_array('OPT_AUTO_SPIN_AutoSentenceTrees', $opt)) {
                            $spinrewriter_api->setAutoSentenceTrees(true);
                        } else {
                            $spinrewriter_api->setAutoSentenceTrees(false);
                        }

                        // (optional) Set the desired spintax format to be used with the returned spun text.
                        $spinrewriter_api->setSpintaxFormat("{|}");

                        // Make the actual API request and save response as a native PHP array.
                        $text = "John will book a room. Then he will read a book by Douglas Adams.";

                        $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

                        $article = $this->replaceExecludes($article, $opt);

                        // fixes
                        $original_article = $article = str_replace(':(*', ': (*', $article);


                        $api_response2 = $spinrewriter_api->getTextWithSpintax($article);

                        // validate reply with OK
                        if (isset($api_response2['status'])) {

                            // status = OK
                            if ($api_response2['status'] == 'OK') {

                                wp_auto_spinner_log_new('SpinRewriter', 'status is ok i.e valid content returned');

                                $article = $api_response2['response'];

                                if ($spinRewriterDebug)
                                    echo '---------------spintax: ' . $article;

                                // fix lrb
                                $article = str_replace('-LRB-', '(', $article);

                                // fix exclude
                                $article = preg_replace('{\(\s(\**?\))\.}', '($1', $article); // ( *****).
                                $article = preg_replace('{\(\s(\**?\))\s\(}', '($1 (', $article); // ( *****) (
                                $article = preg_replace('{\s(\(\**?\))\.(\s)}', "$1$2", $article); // (***********************).\s
                                $article = str_replace('( *', '(*', $article);
                                $article = str_replace('& #', '&#', $article);

                                if ($this->debug)
                                    echo "<br><br>" . time() . "Returned from spinRewriter:-<hr> " . $article;


                                // safe mode
                                if (in_array('OPT_AUTO_SPIN_SR_SAFE', $opt)) {

                                    // extract synonyms from remote post and replace in the original article
                                    $article = $this->find_remote_synonyms_and_replace($article, $original_article);
                                }

                                $article = $this->restoreExecludes($article);

                                if ($spinRewriterDebug)
                                    echo '---------------restoreExcludes: ' . $article;

                                $this->article = $article;

                                // now article contains the synonyms on the form {test|test2}
                                return $this->update_post();
                            } else {
                                wp_auto_spinner_log_new('SpinRewriter says', $api_response2['response']);
                            }
                        } else {
                            wp_auto_spinner_log_new('SpinRewriter', 'We could not get valid response ');
                        }
                    } else {
                        wp_auto_spinner_log_new('SpinRewriter says', $api_response['response']);
                    }
                } else {
                    wp_auto_spinner_log_new('SpinRewriter says', $api_response['response']);
                }
            } else {
                wp_auto_spinner_log_new('spinning', 'Trying to use spinrewriter api');
            }
        } // found email and password

        wp_auto_spinner_log_new('SpinRewriter Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /**
     * OpenAI GPT spinning function
     */
    function spin_openai()
    {

        // log
        wp_auto_spinner_log_new('Spinning', 'Trying to use OpenAI GPT API');

        // openai options
        $wp_auto_spinner_openai_api_key = get_option('wp_auto_spinner_openai_api_key', '');

        // plugin options
        $opt = get_option('wp_auto_spin', array());



        // check if email and password is saved
        if (trim($wp_auto_spinner_openai_api_key) != '') {


            // get article
            $article = '<h1>' . stripslashes($this->title) . '</h1>'   . (stripslashes($this->post));

            //compress the article by masking pictures and images
            $article = $this->openai_compress_article($article);


            // keep a snapshot of the original article
            $original_article = $article;

            // message field 
            $default_message = "Replace words with other synonyms and change sentences structure but keep HTML tags and format as-is. do not change the content language";

            $wp_auto_spinner_openai_system_message = get_option('wp_auto_spinner_openai_system_message', $default_message);

            //strip slashes 
            $wp_auto_spinner_openai_system_message = stripslashes($wp_auto_spinner_openai_system_message);

            //if system message is empty, overwite it with default_message
            if (trim($wp_auto_spinner_openai_system_message) == '') {
                $wp_auto_spinner_openai_system_message = $default_message;
            }


            // tempreature 
            $wp_auto_spinner_openai_tempreature = get_option('wp_auto_spinner_openai_tempreature', '0.9');

            // top_p
            $wp_auto_spinner_openai_top_p = get_option('wp_auto_spinner_openai_top_p', '1');

            // frequency_penalty
            $wp_auto_spinner_openai_frequency_penalty = get_option('wp_auto_spinner_openai_frequency_penalty', '0');

            // presence_penalty
            $wp_auto_spinner_openai_presence_penalty = get_option('wp_auto_spinner_openai_presence_penalty', '0');


            //gpt-4 used or not 
            $opt = get_option('wp_auto_spin', array());

            //if gpt-4 is used
            if (in_array('OPT_AUTO_SPIN_GPT4', $opt)) {
                //set model to  gpt-4-1106-preview
                $args['model'] = 'gpt-4-1106-preview';
            }

            //building $args array for the api_call function
            $args['text'] = $article;
            $args['apiKey'] = $wp_auto_spinner_openai_api_key;
            $args['system'] = $wp_auto_spinner_openai_system_message;

            //if tempreture is set and is a number
            if (is_numeric($wp_auto_spinner_openai_tempreature)) {
                $args['tempreature'] = $wp_auto_spinner_openai_tempreature;
            }

            //if top_p is set and is a number
            if (is_numeric($wp_auto_spinner_openai_top_p)) {
                $args['top_p'] = $wp_auto_spinner_openai_top_p;
            }

            //if frequency_penalty is set and is a number
            if (is_numeric($wp_auto_spinner_openai_frequency_penalty)) {
                $args['frequency_penalty'] = $wp_auto_spinner_openai_frequency_penalty;
            }

            //if presence_penalty is set and is a number
            if (is_numeric($wp_auto_spinner_openai_presence_penalty)) {
                $args['presence_penalty'] = $wp_auto_spinner_openai_presence_penalty;
            }

            //split the text to chunks to send every chunk separately to the api
            //this is because the api is too slow and if we send the whole article at once, it will take too long to return
            $char_count = WP_AUTO_SPINNER_OPENAI_ENGLISH_LIMIT;

            //if option OPT_AUTO_SPIN_OPENAI_NOT_ENGLISH is set, set char_count to WP_AUTO_SPINNER_OPENAI_ENGLISH_LIMIT
            if (in_array('OPT_AUTO_SPIN_OPENAI_NOT_ENGLISH', $opt)) {
                $char_count = WP_AUTO_SPINNER_OPENAI_NON_ENGLISH_LIMIT;
            }

            $chunks = wp_auto_spinner_split_text($article, $char_count);

            //log number of chunks 
            wp_auto_spinner_log_new('OpenAI chunks', 'Content splitted to ' . count($chunks) . ' chunks, chunk size does not excced ' . $char_count . ' characters');

            //loop through chunks
            $c = 0;
            $api_all_chunk_result = ''; //should contain all chunks results concatenated
            $chunk_processing_error_exists = false; //flag to check if all chunks were processed 

            foreach ($chunks as $chunk) {


                //check if chunk was already processed and there is a cache saved in the post meta wp_auto_spinner_chunk_{chunk_md5}
                $chunk_md5 = md5($chunk);

                //if chunk was already processed, use the cached result
                if (get_post_meta($this->id, 'wp_auto_spinner_chunk_' . $chunk_md5, true) != '') {

                    //log
                    wp_auto_spinner_log_new('OpenAI chunk', 'Chunk ' . ($c + 1) . '/' . count($chunks) . ' was already processed, using cached result');

                    //get cached result
                    $api_chunk_result = get_post_meta($this->id, 'wp_auto_spinner_chunk_' . $chunk_md5, true);

                    //add the result to the all chunks result
                    $api_all_chunk_result .= $api_chunk_result;
                } else {

                    //log processing chunk number/total
                    wp_auto_spinner_log_new('OpenAI chunk', 'Processing chunk ' . ($c + 1) . '/' . count($chunks));

                    //set the args text to the current chunk
                    $args['text'] = $chunk;

                    // call the api
                    try {

                        //time start 
                        $api_chunk_start_time = time();

                        //call the API
                        $api_chunk_result = $this->api_call('rewrite', $args);

                        //add the result to the all chunks result
                        $api_all_chunk_result .= $api_chunk_result;

                        //save the result in the post meta
                        update_post_meta($this->id, 'wp_auto_spinner_chunk_' . $chunk_md5, $api_chunk_result);

                        //time used to process this chunk
                        $api_chunk_time = time() - $api_chunk_start_time;

                        //log
                        wp_auto_spinner_log_new('OpenAI chunk', 'Chunk ' . ($c + 1) . '/' . count($chunks) . ' processed in ' . $api_chunk_time . ' seconds');
                    } catch (Exception $e) {
                        wp_auto_spinner_log_new('OpenAI GPT', 'Error: ' . $e->getMessage());

                        //set the flag to true
                        $chunk_processing_error_exists = true;

                        //break the loop
                        break;
                    }
                }

                //increase counter
                $c++;
            }

            //now all chunks are processed, echo the result

            //check if all chunks were processed successfully
            if ($chunk_processing_error_exists == false) {

                //log 
                wp_auto_spinner_log_new('OpenAI GPT', 'All chunks were processed successfully for pid:' . $this->id);
                $api_response = $api_all_chunk_result;
            } else {

                //log one or more chunks were not processed successfully
                wp_auto_spinner_log_new('OpenAI GPT', 'One or more chunks were not processed successfully for pid:' . $this->id . ' You can try again!');

                //no failover just return 
                return false;
                return $this->spin();
            }

            //trim
            $api_response = trim($api_response);

            //restore images
            $api_response = $this->openai_restore_images($api_response);

            // debug
            if ($this->debug) {
                echo "\n\n ------------ api_response  ------------ \n";
                print_r($api_response);
            }

            //replace first H1 tag with the content of the h1 tag using regex

            //if response contains <h1> tag, replace
            if (strpos($api_response, '<h1>') !== false) {
                $restored_article = preg_replace('/<h1>(.*?)<\/h1>/', "$1 911911 " . '', $api_response, 1);
            } else {

                //h1 tag not found, set restored article to title and api response separated by 911911
                $restored_article = $this->title . ' 911911 ' . $api_response;

                //log this event
                wp_auto_spinner_log_new('OpenAI GPT', 'OpenAI GPT did not return an H1 tag, we will use the title instead');
            }

            $this->article = $restored_article;

            // report success
            wp_auto_spinner_log_new('OpenAI Success', 'OpenAI returned content successfully pid:#' . $this->id);

            // delete the cached chunks
            $this->delete_cached_chunks();

            // now article contains the synonyms on the form {test|test2}
            return $this->update_post();
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('OpenAI GPT Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /**
     * Function delete_cached_chunks : delete the cached chunks
     */
    function delete_cached_chunks()
    {

        //get all post meta
        $post_meta = get_post_meta($this->id);

        //loop through post meta
        foreach ($post_meta as $key => $value) {

            //if key starts with wp_auto_spinner_chunk_
            if (strpos($key, 'wp_auto_spinner_chunk_') !== false) {

                //delete the post meta
                delete_post_meta($this->id, $key);
            }
        }
    }

    /**
     * WordAI spinning function
     */
    function spin_wordai()
    {

        $wordAiDebug = false;

        wp_auto_spinner_log_new('Spinning', 'Trying to use WordAi API Rewrite mode');

        // wordai options
        $wp_auto_spinner_wordai_email = trim(get_option('wp_auto_spinner_wordai_email', ''));
        $wp_auto_spinner_wordai_password = trim(get_option('wp_auto_spinner_wordai_password', ''));
        $wp_auto_spinner_wordai_uniqueness = trim(get_option('wp_auto_spinner_wordai_uniqueness', '1'));

        // migrate old quality from 1-100
        if ($wp_auto_spinner_wordai_uniqueness != '2' && $wp_auto_spinner_wordai_uniqueness != '3')
            $wp_auto_spinner_wordai_uniqueness = '1';

        $opt = get_option('wp_auto_spin', array());

        // check if email and password is saved
        if (trim($wp_auto_spinner_wordai_email) != '' && trim($wp_auto_spinner_wordai_password) != '') {

            // good we now have an email and password let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

            //WordAI replaces “ and ” with " and " which is not good for us, so we will replace them with normal quotes " and " before spinning
            $article = str_replace('”', '"', $article);
            $article = str_replace('“', '"', $article);

            //WordAI replaces ’ with ' which is not good for us, so we will replace them with normal quotes ' and ' before spinning
            $article = str_replace('’', "'", $article);


            // mask the execluded parts with astrics
            $article = $this->replaceExecludes($article, $opt);

            $original_article = $article;



            $article = preg_replace('!(\(\**\))!', "\n\n\n", $article);
            $article = str_replace('911911', "\n\n911911\n\n", $article);


            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            if ($wordAiDebug)
                print_r('Article:' . $article);

            // curl post
            $curlurl = "https://wai.wordai.com/api/rewrite";

            $curlpost = "s=" . urlencode($article) . "&quality=" . urlencode(trim($wp_auto_spinner_wordai_quality)) . "&email=$wp_auto_spinner_wordai_email&pass=$wp_auto_spinner_wordai_password&output=json&nonested=on";
            $curlpost = "email=$wp_auto_spinner_wordai_email&key=$wp_auto_spinner_wordai_password&input=" . urlencode($article) . "&rewrite_num=2"; // q=urlencode(data)
            $curlpost .= '&uniqueness=' . $wp_auto_spinner_wordai_uniqueness;



            curl_setopt($ch, CURLOPT_URL, $curlurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $exec = curl_exec($ch);
            $x = curl_error($ch);


            // validate result

            if (stristr($exec, '{')) {

                // good it is json let's verify
                $jsonReply = json_decode($exec);

                // verify status either success or failure
                if (isset($jsonReply->status)) {

                    if ($jsonReply->status == 'Success') {

                        if ($wordAiDebug)
                            print_r(' ---------- Return:' . $jsonReply->text);

                        // fix -LRB-
                        $jsonReply->text = str_replace('-LRB-', '(', $jsonReply->text);

                        // fix {*|}
                        $jsonReply->text = preg_replace("/{\*\|.*?}/", '*', $jsonReply->text);

                        // fix {) have|)'ve}
                        preg_match_all('/{\)[^}]*\|\)[^}]*}/', $jsonReply->text, $matches_brackets);
                        $matches_brackets = $matches_brackets[0];

                        foreach ($matches_brackets as $matches_bracket) {
                            $matches_bracket_clean = str_replace(array(
                                '{',
                                '}'
                            ), '', $matches_bracket);
                            $matches_bracket_parts = explode('|', $matches_bracket_clean);
                            $jsonReply->text = str_replace($matches_bracket, $matches_bracket_parts[0], $jsonReply->text);
                        }

                        //fix {911911|911911|911911}
                        $jsonReply->text = str_replace('{911911|911911|911911}', '911911', $jsonReply->text);

                        //fix space gets removed from synonyms { now in week five of|Now in week five|We are now in week five}
                        $jsonReply->text = str_replace('{ ', ' {',  $jsonReply->text);

                        //fix quotes are split and removed from the end of the text

                        $article = $jsonReply->text;

                        if (in_array('OPT_AUTO_SPIN_WORDAI_PERFECT', $opt)) {

                            $article = str_replace(' \n ', "\n", $article);
                            $article = str_replace(' \N ', "\n", $article);
                            $article = str_replace(')\n(', ")\n(", $article);

                            $article = stripslashes($article);
                        }

                        // safe mode
                        // extract synonyms from remote post and replace in the original article
                        $article = $this->find_remote_synonyms_and_replace($article, $original_article, true);


                        // good the wordai spinned the content successfully
                        $this->article = $this->restoreExecludes($article);

                        //restore spaces removed by WordAI ex 
                        //ump. As Blizzard says in its own turned to 

                        // report success
                        wp_auto_spinner_log_new('WordAI Success', 'WordAI returned content successfully pid:#' . $this->id);

                        // now article contains the synonyms on the form {test|test2}
                        return $this->update_post();
                    } elseif ($jsonReply->status == 'Failure') {
                        wp_auto_spinner_log_new('WordAI Err', 'WordAI returned an error: ' . $jsonReply->error);
                    } else {

                        wp_auto_spinner_log_new('WordAI Err', 'Unknown status ' . $jsonReply->status);
                    }
                } else {
                    wp_auto_spinner_log_new('WordAI Err', 'Can not find reply status with decoded json');
                }
            } else {
                wp_auto_spinner_log_new('WordAI Err', 'We issued the request but the response does not contain expected json ' . $x);
            } // response does not even contain json
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('WordAI Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /**
     * WordAI spinning function Avoid mode
     */
    function spin_wordai_avoid()
    {

        $wordAiDebug = false;

        wp_auto_spinner_log_new('Spinning', 'Trying to use WordAi API Avoid AI Detection mode');

        // wordai options
        $wp_auto_spinner_wordai_email = trim(get_option('wp_auto_spinner_wordai_email', ''));
        $wp_auto_spinner_wordai_password = trim(get_option('wp_auto_spinner_wordai_password', ''));
        $wp_auto_spinner_wordai_mode = trim(get_option('wp_auto_spinner_wordai_mode', 'change_more'));

        // options
        $opt = get_option('wp_auto_spin', array());

        // check if email and password is saved
        if (trim($wp_auto_spinner_wordai_email) != '' && trim($wp_auto_spinner_wordai_password) != '') {

            // good we now have an email and password let's try

            // get article which is the post content only
            $article =  (stripslashes($this->post));

            //WordAI replaces “ and ” with " and " which is not good for us, so we will replace them with normal quotes " and " before spinning
            $article = str_replace('”', '"', $article);
            $article = str_replace('“', '"', $article);

            //WordAI replaces ’ with ' which is not good for us, so we will replace them with normal quotes ' and ' before spinning
            $article = str_replace('’', "'", $article);



            $original_article = $article;

            //if wordAiDebug is true, print article
            if ($wordAiDebug)
                print_r(' -------------------------- Original Article ----------------------------' . "\n" . $article);

            //remove all html tags
            $article = strip_tags($article);

            // print 
            if ($wordAiDebug)
                print_r("\n\n" . ' -------------------------- Article without HTML tags ----------------------------' . "\n" . $article);


            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.



            // curl post
            $curlurl = "https://wai.wordai.com/api/avoid";

            $curlpost = "email=$wp_auto_spinner_wordai_email&key=$wp_auto_spinner_wordai_password&input=" . urlencode($article) . "&mode=" . $wp_auto_spinner_wordai_mode; // q=urlencode(data)


            curl_setopt($ch, CURLOPT_URL, $curlurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $exec = curl_exec($ch);
            $x = curl_error($ch);


            // validate result

            if (stristr($exec, '{')) {

                // good it is json let's verify
                $jsonReply = json_decode($exec);

                // verify status either success or failure
                if (isset($jsonReply->status)) {

                    if ($jsonReply->status == 'Success') {

                        if ($wordAiDebug)
                            print_r("\n\n" . ' -------------------------- Article Returned ----------------------------' . "\n" .  $jsonReply->text);

                        $article = $jsonReply->text;

                        //log content returned by wordai
                        wp_auto_spinner_log_new('WordAI', 'WordAI returned content successfully, Sending the title.... pid:#' . $this->id);

                        //do the same for the title, send it for avoid detection 
                        $title = stripslashes($this->title);

                        if (trim($title) != "") {

                            $curlpost = "email=$wp_auto_spinner_wordai_email&key=$wp_auto_spinner_wordai_password&input=" . urlencode($title) . "&mode=" . $wp_auto_spinner_wordai_mode; // q=urlencode(data)			 

                            curl_setopt($ch, CURLOPT_URL, $curlurl);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                            $exec = curl_exec($ch);
                            $x = curl_error($ch);

                            // validate result
                            if (stristr($exec, '"Success"')) {

                                //report success
                                wp_auto_spinner_log_new('WordAI', 'WordAI returned the title successfully pid:#' . $this->id);

                                //get the title returned by wordai
                                $title = json_decode($exec)->text;

                                //if wordAiDebug is true, print title
                                if ($wordAiDebug)
                                    print_r("\n\n" . ' -------------------------- Title Returned ----------------------------' . "\n" .  $title);

                                //replace the title in the article


                            }
                        }

                        //null to br nltobr
                        $article = nl2br($article);

                        //build the final article from title . 911911 . content
                        $article = $title . '911911' . $article;

                        $this->article = $article;

                        //restore spaces removed by WordAI ex 
                        //ump. As Blizzard says in its own turned to 

                        // report success
                        wp_auto_spinner_log_new('WordAI Success', 'WordAI spinned the post successfully pid:#' . $this->id);

                        // now article contains the synonyms on the form {test|test2}
                        return $this->update_post();
                    } elseif ($jsonReply->status == 'Failure') {
                        wp_auto_spinner_log_new('WordAI Err', 'WordAI returned an error: ' . $jsonReply->error);
                    } else {

                        wp_auto_spinner_log_new('WordAI Err', 'Unknown status ' . $jsonReply->status);
                    }
                } else {
                    wp_auto_spinner_log_new('WordAI Err', 'Can not find reply status with decoded json');
                }
            } else {
                wp_auto_spinner_log_new('WordAI Err', 'We issued the request but the response does not contain expected json ' . $x);
            } // response does not even contain json
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('WordAI Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /**
     * TheBestSPinner spinning function
     */
    function spin_tbs()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use TBS api');

        // TBS options
        $wp_auto_spinner_tbs_email = get_option('wp_auto_spinner_tbs_email', '');
        $wp_auto_spinner_tbs_password = get_option('wp_auto_spinner_tbs_password', '');
        $wp_auto_spinner_tbs_maxsyns = get_option('wp_auto_spinner_tbs_maxsyns', '');

        if (!is_numeric($wp_auto_spinner_tbs_maxsyns) && $wp_auto_spinner_tbs_maxsyns > 0) {
            $wp_auto_spinner_tbs_maxsyns = 3;
        }

        $wp_auto_spinner_tbs_quality = get_option('wp_auto_spinner_tbs_quality', '');

        if ($wp_auto_spinner_tbs_quality != 1 && $wp_auto_spinner_tbs_quality != 2 && $wp_auto_spinner_tbs_quality != 3) {
            $wp_auto_spinner_tbs_quality = 3;
        }

        $tbs_protected = get_option('wp_auto_spinner_execlude', '');

        $opt = get_option('wp_auto_spin', array());

        // check if email and password is saved
        if (trim($wp_auto_spinner_tbs_email) != '' && trim($wp_auto_spinner_tbs_password) != '') {

            // good we now have an email and password let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));
            $article = $this->replaceExecludes($article, $opt);

            // $article ='Here is an example.';

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            $url = 'http://thebestspinner.com/api.php';

            $testmethod = 'identifySynonyms';
            $testmethod = 'replaceEveryonesFavorites';

            // Build the data array for authenticating.

            $data = array();
            $data['action'] = 'authenticate';
            $data['format'] = 'php'; // You can also specify 'xml' as the format.

            // The user credentials should change for each UAW user with a TBS account.

            if (trim($tbs_protected) != '') {
                $tbs_protected = explode("\n", $tbs_protected);
                $tbs_protected = array_filter($tbs_protected);
                $tbs_protected = array_map('trim', $tbs_protected);
                $tbs_protected = implode(',', $tbs_protected);
            }

            // add , if not exists
            if (stristr($tbs_protected, ',')) {
                $tbs_protected = $tbs_protected . ',';
            }

            $data['username'] = $wp_auto_spinner_tbs_email;
            $data['password'] = $wp_auto_spinner_tbs_password;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $exec = curl_exec($ch);
            $x = curl_error($ch);


            if (stristr($exec, 'session')) {

                // good it is unsersialzed array verify
                $exec = unserialize($exec);

                // verify status either success or failure
                if (isset($exec['success'])) {

                    if ($exec['success'] == true) {
                        // good we got valid session to use
                        $session = $exec['session'];

                        // Build the data array for the example.
                        $data = array();
                        $data['session'] = $session;
                        $data['format'] = 'php'; // You can also specify 'xml' as the format.
                        $data['protectedterms'] = $tbs_protected;
                        $data['text'] = ($article);
                        $data['action'] = $testmethod;
                        $data['maxsyns'] = $wp_auto_spinner_tbs_maxsyns; // The number of synonyms per term.

                        if ($testmethod == 'replaceEveryonesFavorites') {
                            // Add a quality score for this method.
                            $data['quality'] = $wp_auto_spinner_tbs_quality;
                        }

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        $exec = curl_exec($ch);
                        $x = curl_error($ch);

                        // echo $exec.$x;
                        // exit;

                        if (stristr($exec, 'a:')) {

                            $exec = unserialize($exec);

                            // valid serialized reply array
                            if ($exec['success'] == true) {

                                // good successfully spinned article here
                                $this->article = $this->restoreExecludes($exec['output']);

                                // report success
                                wp_auto_spinner_log_new('TBS Success', 'TBS returned content successfully pid:#' . $this->id);

                                // now article contains the synonyms on the form {test|test2}
                                return $this->update_post();
                            } else {

                                if (isset($exec['error'])) {
                                    wp_auto_spinner_log_new('TBS Err', 'login success but spin request returned an error:' . $exec['error']);
                                } else {
                                    wp_auto_spinner_log_new('TBS Err', 'niether success or error ');
                                }
                            }
                        } else {

                            wp_auto_spinner_log_new('TBS Err', 'login success but spin request did not return valid unserialized array');
                        }
                    } elseif (isset($exec['error'])) {
                        wp_auto_spinner_log_new('TBS Err', 'Login status is not success:' . $exec['error']);
                    } else {
                        wp_auto_spinner_log_new('TBS Err', 'can not find success or error');
                    }
                } else {
                    wp_auto_spinner_log_new('TBS Err', 'Can not find reply status with decoded Arr');
                }
            } else {

                if (stristr($exec, 'Invalid username')) {
                    wp_auto_spinner_log_new('TBS Err', 'Login failed, Invalid username or password.');
                } else {
                    wp_auto_spinner_log_new('TBS Err', 'We issued the LOGIN request but the response does not contain valid authentication ' . $exec);
                }
            } // response does not even contain json
        } // no email or password saved

        // failed to use wordai

        wp_auto_spinner_log_new('TBS Skip', 'We will use the internal synonyms database instead');

        return $this->spin();
    }

    /*
	 * ContentProfessor spinning function
	 *
	 */
    function spin_cp()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use ContentProfessor api');

        // CP options
        $wp_auto_spinner_cp_email = get_option('wp_auto_spinner_cp_email', '');
        $wp_auto_spinner_cp_password = get_option('wp_auto_spinner_cp_password', '');
        $wp_auto_spinner_cp_language = get_option('wp_auto_spinner_cp_language', 'en');
        $wp_auto_spinner_cp_limit = get_option('wp_auto_spinner_cp_limit', '5');
        $wp_auto_spinner_cp_quality = get_option('wp_auto_spinner_cp_quality', 'ideal');
        $wp_auto_spinner_cp_synonym_set = get_option('wp_auto_spinner_cp_synonym_set', 'global');
        $wp_auto_spinner_cp_min_words_count = get_option('wp_auto_spinner_cp_min_words_count', '1');
        $wp_auto_spinner_cp_max_words_count = get_option('wp_auto_spinner_cp_max_words_count', '7');
        $wp_auto_spinner_cp_type = get_option('wp_auto_spinner_cp_type', 'free');

        $opt = get_option('wp_auto_spin', array());

        // check if email and password is saved
        if (trim($wp_auto_spinner_cp_email) != '' && trim($wp_auto_spinner_cp_password) != '') {

            // good we now have an email and password let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            // build session url
            $url = 'http://www.contentprofessor.com/member_' . $wp_auto_spinner_cp_type . '/api/get_session?format=json&login=' . trim($wp_auto_spinner_cp_email) . '&password=' . trim($wp_auto_spinner_cp_password);

            // process request
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            $exec = curl_exec($ch);
            $x = curl_error($ch);

            if (stristr($exec, '{')) {

                // good it is unsersialzed array verify
                $exec = json_decode($exec);

                // verify status either success or failure
                if (isset($exec->result)) {

                    if (isset($exec->result->data->session)) {

                        // good we got valid session to use
                        $session = $exec->result->data->session;

                        $article = $this->replaceExecludes($article, $opt);

                        $url = "http://www.contentprofessor.com/member_" . $wp_auto_spinner_cp_type . "/api/include_synonyms?format=json&session=" . $session . "&language=$wp_auto_spinner_cp_language&limit=$wp_auto_spinner_cp_limit&quality=$wp_auto_spinner_cp_quality&synonym_set=$wp_auto_spinner_cp_synonym_set&min_words_count=$wp_auto_spinner_cp_min_words_count&max_words_count=$wp_auto_spinner_cp_max_words_count";

                        if (in_array('OPT_AUTO_SPIN_CP_REMOVAL', $opt)) {
                            $url = $url + '&removal_on=1';
                        }

                        if (in_array('OPT_AUTO_SPIN_CP_EXECLUDE', $opt)) {
                            $url = $url + '&excludes_on=1';
                        }

                        if (in_array('OPT_AUTO_SPIN_CP_STOP', $opt)) {
                            $url = $url + '&exclude_stop_words=1';
                        }

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);

                        $curlpost = array(
                            'text' => $article
                        );
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);

                        $exec = curl_exec($ch);
                        $x = curl_error($ch);

                        if (stristr($exec, '{')) {

                            $exec = json_decode($exec);

                            // valid json decoded reply array
                            if (isset($exec->result->data->text)) {

                                // good successfully spinned article here

                                $article = preg_replace('{<span class="word" id=".*?">(.*?)</span>}su', "$1", $exec->result->data->text);

                                $this->article = $this->restoreExecludes($article);

                                // report success
                                wp_auto_spinner_log_new('CP Success', 'CP returned content successfully pid:#' . $this->id);

                                // now article contains the synonyms on the form {test|test2}
                                return $this->update_post();
                            } else {

                                if (isset($exec->result->error->description)) {
                                    wp_auto_spinner_log_new('CP Err', 'login success but spin request returned an error:' . $exec->result->error->description);
                                } else {
                                    wp_auto_spinner_log_new('CP Err', 'niether success or error ');
                                }
                            }
                        } else {

                            wp_auto_spinner_log_new('CP Err', 'We issued the Rewrite request but the response does not contain expected valid json');
                        }
                    } elseif (isset($exec->result->error->description)) {
                        wp_auto_spinner_log_new('CP Err', 'Login status is not success:' . $exec->result->error->description);
                    } else {
                        wp_auto_spinner_log_new('CP Err', 'can not find success or error');
                    }
                } else {
                    wp_auto_spinner_log_new('CP Err', 'Can not find reply result with decoded Json');
                }
            } else {
                wp_auto_spinner_log_new('ContentProfessor Err', 'We issued the LOGIN request but the response does not contain expected valid json');
            } // response does not even contain json
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('SpinRewriter Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /**
     * Spinner chief spinning function
     */
    function spin_sc()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use SpinnerChief api');

        // sc spinnerchief
        $wp_auto_spinner_sc_api_key = trim(get_option('wp_auto_spinner_sc_api_key', ''));
        $wp_auto_spinner_sc_dev_key = trim(get_option('wp_auto_spinner_sc_dev_key', ''));

        $opt = get_option('wp_auto_spin', array());

        // check if api key and dev key
        if (trim($wp_auto_spinner_sc_api_key) != '' && trim($wp_auto_spinner_sc_dev_key) != '') {


            // get article
            $article = '<h1>' . stripslashes($this->title) . '</h1>' . (stripslashes($this->post));

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            // build session url
            $url = "https://spinnerchief.com/api/paraphraser";

            /* build post data
			{
				"api_key":"",
				"dev_key":"",
				"text":""
			  }
			*/

            $payload = array(
                "api_key" => $wp_auto_spinner_sc_api_key,
                "dev_key" => $wp_auto_spinner_sc_dev_key,
                "text" => $article
            );

            //post 
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($payload));
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $exec = curl_exec($ch);
            $err = curl_error($ch);

            //if exec contains { then it is json}
            if (stristr($exec, '{')) {
                $json = json_decode($exec);
            }


            if (trim($err) != '') {
                wp_auto_spinner_log_new('SpinnerChief err', $err);
            } elseif (!stristr($exec, '{')) {
                wp_auto_spinner_log_new('SpinnerChief err', 'No json returned');
            } elseif ($json->code !== 200) {
                wp_auto_spinner_log_new('SpinnerChief err', 'Failed code ' . $json->code . ' message: ' . $json->text);
            } else {

                $response_article = $json->text;

                //replace the h1 with the text inside it using REGEX
                $pattern = '/<h1>(.*?)<\/h1>/';
                $replacement = '${1} 911911 ';
                $response_article = preg_replace($pattern, $replacement, $response_article);


                // good we have the article found
                $this->article = $response_article;

                // report success
                wp_auto_spinner_log_new('SpinnerChief Success', 'SpinnerChief returned content successfully pid:#' . $this->id);

                // now article contains the synonyms on the form {test|test2}
                return $this->update_post();
            } // no error
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('SpinnerChief Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /*
	 * ChimpRewriter spinning function
	 *
	 */
    function spin_cr()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use  ChimpRewriter api');

        // cr chimprewriter
        $wp_auto_spinner_cr_email = get_option('wp_auto_spinner_cr_email', '');
        $wp_auto_spinner_cr_apikey = get_option('wp_auto_spinner_cr_apikey', '');
        $wp_auto_spinner_cr_quality = get_option('wp_auto_spinner_cr_quality', '4');
        $wp_auto_spinner_cr_phrasequality = get_option('wp_auto_spinner_cr_phrasequality', '3');
        $wp_auto_spinner_cr_posmatch = get_option('wp_auto_spinner_cr_posmatch', '3');

        $opt = get_option('wp_auto_spin', array());

        // check if email and password is saved
        if (trim($wp_auto_spinner_cr_email) != '' && trim($wp_auto_spinner_cr_apikey) != '') {

            // good we now have an email and password let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));
            $article = $this->replaceExecludes($article, $opt);

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $curlurl = "https://api.chimprewriter.com/ChimpRewrite";
            $curlpost = "email=" . trim($wp_auto_spinner_cr_email) . "&apikey=" . trim($wp_auto_spinner_cr_apikey) . "&quality=" . $wp_auto_spinner_cr_quality . "&text=" . urlencode($article) . "&aid=none&tagprotect=[|]";

            $curlpost = $curlpost . '&phrasequality=' . $wp_auto_spinner_cr_phrasequality;
            $curlpost = $curlpost . '&posmatch=' . $wp_auto_spinner_cr_posmatch;

            // sentense rewrite
            if (in_array('OPT_AUTO_SPIN_CR_SREWRITE', $opt)) {
                $curlpost = $curlpost . '&sentencerewrite=1';
            }

            if (in_array('OPT_AUTO_SPIN_CR_GCHECK', $opt)) {
                $curlpost = $curlpost . '&grammarcheck=1';
            }

            if (in_array('OPT_AUTO_SPIN_CR_reorderparagraphs', $opt)) {
                $curlpost = $curlpost . '&reorderparagraphs=1';
            }

            if (in_array('OPT_AUTO_SPIN_CR_replacephraseswithphrases', $opt)) {
                $curlpost = $curlpost . '&replacephraseswithphrases=1';
            }

            if (in_array('OPT_AUTO_SPIN_CR_spintidy', $opt)) {
                $curlpost = $curlpost . '&spintidy=0';
            }

            curl_setopt($ch, CURLOPT_URL, $curlurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);

            $exec = curl_exec($ch);
            $x = curl_error($ch);

            if (stristr($exec, '{')) {

                // good it is unsersialzed array verify
                $exec = json_decode($exec);

                // verify status either success or failure
                if (isset($exec->status)) {

                    if (isset($exec->output) && trim($exec->status) == 'success') {

                        // good successfully spinned article here
                        $this->article = $this->restoreExecludes($exec->output);

                        // report success
                        wp_auto_spinner_log_new('CR Success', 'CP returned content successfully pid:#' . $this->id);

                        // now article contains the synonyms on the form {test|test2}
                        return $this->update_post();
                    } elseif (trim($exec->status) == 'failure') {
                        wp_auto_spinner_log_new('CR Err', 'Login status is not success:' . $exec->output);
                    } else {
                        wp_auto_spinner_log_new('CR Err', 'can not find success or error');
                    }
                } else {
                    wp_auto_spinner_log_new('CR Err', 'Can not find reply result with decoded Json');
                }
            } else {
                wp_auto_spinner_log_new('ChimpRewriter Err', 'We issued the LOGIN request but the response does not contain expected valid json');
            } // response does not even contain json
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('SpinRewriter Skip', 'We will use the internal synonyms database instead');
        return $this->spin();
    }

    /*
	 * Espinner spinning function
	 */
    function spin_es()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use ESpinner api');

        // es options
        $wp_auto_spinner_es_password = get_option('wp_auto_spinner_es_password', '');
        $wp_auto_spinner_es_email = get_option('wp_auto_spinner_es_email', '');

        // spin options
        $opt = get_option('wp_auto_spin', array());

        // check if email and password are saved
        if (trim($wp_auto_spinner_es_email) != '' && trim($wp_auto_spinner_es_password) != '') {

            // good we now have an email and password let's try

            // get article
            $article = stripslashes($this->title) . ' #### ' . (stripslashes($this->post));

            // replace html, shortcodes and so
            $article = $this->replaceExecludes($article, $opt);




            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            // build session url
            $url = 'http://espinner.net/app/api/spinner';

            // build parameters
            $params = 'content=' . urlencode($article);
            $params .= '&email=' . $wp_auto_spinner_es_email . '&apikey=' . $wp_auto_spinner_es_password;

            // process request
            // curl post
            $curlurl = "$url";
            $curlpost = (($params));
            curl_setopt($ch, CURLOPT_URL, $curlurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);
            $exec = curl_exec($ch);
            $x = curl_error($ch);

            if (!stristr($exec, '{')) {

                wp_auto_spinner_log_new('Espinner err', 'Did not get valid response from Espinner ' . $exec . $x);
            } else {

                // valid json
                $json = json_decode($exec);

                if (isset($json->error) && trim($json->error) != '') {

                    wp_auto_spinner_log_new('Espinner err', $json->error);
                } elseif (isset($json->spintax)) {

                    wp_auto_spinner_log_new('Espinner success', 'Espinner returned the content successfully pid:#' . $this->id . ' limit ' . $json->limit);

                    $article = str_replace(' #### ', ' 911911 ',  $article);

                    $article = $this->find_remote_synonyms_and_replace($json->spintax, $article);

                    // restoring ****
                    $article = $this->restoreExecludes($article);

                    // good we have the article found
                    $this->article = $article;

                    // now article contains the synonyms on the form {test|test2}
                    return $this->update_post();
                } else {
                    // not valid json
                    wp_auto_spinner_log_new('Espinner err', 'Invalid reply ' . $exec);
                }
            }
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('ESpinner Skip', 'We will use the internal synonyms database instead ');
        return $this->spin();
    }

    /**
     * spinbot.com
     */
    function spin_bot()
    {
        wp_auto_spinner_log_new('spinning', 'Trying to use SpinBot api');

        // sbot options
        $wp_auto_spinner_bot_key = trim(get_option('wp_auto_spinner_bot_key', ''));

        // spin options
        $opt = get_option('wp_auto_spin', array());

        // check if key exists
        if (trim($wp_auto_spinner_bot_key) != '') {

            // good we now have an API key let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

            // replace html, shortcodes and so
            $article = $this->replaceExecludes($article, $opt);

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            // auth
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "x-auth-key: $wp_auto_spinner_bot_key"
            ));

            // curl post
            $curlpost = $article;
            curl_setopt($ch, CURLOPT_URL, 'https://api.spinbot.com');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlpost);
            $x = 'error';
            $exec = curl_exec($ch);

            $exec = str_replace("HTTP/1.1 100 Continue\r\n\r\n", '', $exec);

            $x = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $exec_parts = explode("\r\n\r\n", $exec, 2);

            $exec_headers = $this->putHeadersTextIntoArray($exec_parts[0]);
            $exec = $exec_parts[1];

            if (trim($exec) == '' && trim($x) != '') {

                wp_auto_spinner_log_new('SpinBot err', 'Connection error ' . $x);
            } elseif (trim($exec) == '' || $http_code != 200) {

                wp_auto_spinner_log_new('SpinBot err', 'Did not get valid response from SpinBot Code ' . $http_code);
            } elseif (isset($exec_headers['spinbot-error'])) {

                wp_auto_spinner_log_new('SpinBot err', $exec_headers['spinbot-error']);
            } elseif (!isset($exec_headers['available-spins'])) {

                wp_auto_spinner_log_new('SpinBot err', 'Can not find available-spin header');
            } else {

                wp_auto_spinner_log_new('SpinBot success', 'SpinBot returned the content successfully pid:#' . $this->id . ' available credit is ' . $exec_headers['available-spins']);

                // $article = $this->find_remote_synonyms_and_replace( $json->spintax ,$article);

                // restoring ****
                $article = $this->restoreExecludes($exec);

                // good we have the article found
                $this->article = $article;

                // now article contains the synonyms on the form {test|test2}
                return $this->update_post();
            }
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('SpinBot Skip', 'We will use the internal synonyms database instead ');
        return $this->spin();
    }

    /**
     * smodin https://rapidapi.com/smodin/api/rewriter-paraphraser-text-changer-multi-language/
     */
    function spin_rp()
    {
        $debug = false;

        wp_auto_spinner_log_new('Spinning...', 'Trying to use smodin rewriter API');

        // Qbot options
        $wp_auto_spinner_rp_key = trim(get_option('wp_auto_spinner_rp_key', ''));

        // spin options
        $opt = get_option('wp_auto_spin', array());

        // check if key exists
        if (trim($wp_auto_spinner_rp_key) != '') {

            // good we now have an API key let's try
            $wp_auto_spinner_rp_lang = trim(get_option('wp_auto_spinner_rp_lang', 'en'));

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

            // replace html, shortcodes and so
            $article = $this->replaceExecludes($article, $opt);



            if ($debug) {
                echo '#####TEXT To SEND:::::::';
                print_r($article);
            }


            $post = array();
            $post['language'] = $wp_auto_spinner_rp_lang;
            $post['strength'] = 3;
            $post['text'] = $article;

            $json = json_encode($post);

            // curl ini
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://rewriter-paraphraser-text-changer-multi-language.p.rapidapi.com/rewrite",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>  $json,
                CURLOPT_HTTPHEADER => [
                    "X-RapidAPI-Host: rewriter-paraphraser-text-changer-multi-language.p.rapidapi.com",
                    "X-RapidAPI-Key: {$wp_auto_spinner_rp_key}",
                    "content-type: application/json"
                ],
            ]);

            $exec = curl_exec($curl);
            $err = curl_error($curl);



            $json = json_decode($exec);

            if ($debug) {
                echo '#####RETURNED JSON:::::::';
                print_r($json);
            }


            if (trim($exec) == '' && trim($err) != '') {
                wp_auto_spinner_log_new('smodin err', 'Connection error ' . $err);
            } elseif (trim($exec) == '') {
                wp_auto_spinner_log_new('smodin err', 'Did not get valid response from smodin empty reply ');
            } elseif (!stristr($exec, '"rewrite"')) {
                wp_auto_spinner_log_new('smodin err',  $exec);
            } else {

                wp_auto_spinner_log_new('smodin success', 'smodin returned the content successfully pid:#' . $this->id);

                $article = $json->rewrite;

                // restoring ****
                $article = $this->restoreExecludes($article);


                // good we have the article found
                $this->article = $article;

                // now article contains the synonyms on the form {test|test2}
                return $this->update_post();
            }
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('smodin rewriter Skip', 'We will use the internal synonyms database instead ');
        return $this->spin();
    }

    /**
     * spinbot.com
     */
    function spin_quillbot()
    {

        $debug = false;

        wp_auto_spinner_log_new('Spinning...', 'Trying to use QuillBot API');

        // Qbot options
        $wp_auto_spinner_qu_key = trim(get_option('wp_auto_spinner_qu_key', ''));

        // spin options
        $opt = get_option('wp_auto_spin', array());

        // check if key exists
        if (trim($wp_auto_spinner_qu_key) != '') {

            // good we now have an API key let's try

            // get article
            $article = stripslashes($this->title) . ' 911911 ' . (stripslashes($this->post));

            // replace html, shortcodes and so
            $article = $this->replaceExecludes($article, $opt);



            // remove excludes with spaces to make multiple sentenses 
            $article_sentences_separated = preg_replace("{\(\**?\)}", ".\n\n",  $article);
            $article_sentences_separated = str_replace("911911", ".\n",  $article_sentences_separated);
            $article_sentences_separated = preg_replace('!\.\s*\.!s', ".",  $article_sentences_separated);

            if ($debug) {
                echo '#####TEXT To SEND:::::::';
                print_r($article_sentences_separated);
            }

            // curl ini
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.

            // auth

            $request = array();
            $request['text'] = $article_sentences_separated;
            $request['numParaphrases'] = 1;
            $request['includeSegs'] = false;



            curl_setopt_array($ch, [
                CURLOPT_URL => "https://quillbot.p.rapidapi.com/paraphrase-all",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>  json_encode($request),
                CURLOPT_HTTPHEADER => [
                    "content-type: application/json",
                    "x-rapidapi-host: quillbot.p.rapidapi.com",
                    "x-rapidapi-key: $wp_auto_spinner_qu_key"
                ],
            ]);

            $exec = curl_exec($ch);
            $err = curl_error($ch);


            $json = json_decode($exec);

            if ($debug) {
                echo '#####RETURNED JSON:::::::';
                print_r($json);
            }

            if (trim($exec) == '' && trim($err) != '') {
                wp_auto_spinner_log_new('QuillBot err', 'Connection error ' . $err);
            } elseif (trim($exec) == '') {
                wp_auto_spinner_log_new('QuillBot err', 'Did not get valid response from SpinBot empty reply ');
            } elseif (stristr($exec, '{"message"')) {
                wp_auto_spinner_log_new('QuillBot err',  $exec);
            } elseif (!stristr($exec, 'original')) {
                wp_auto_spinner_log_new('QuillBot err',  $exec);
            } else {

                wp_auto_spinner_log_new('QuillBot success', 'QuillBot returned the content successfully pid:#' . $this->id);

                if (is_array($json)) {


                    $synonyms_stack = '';
                    foreach ($json as $json_prt) {

                        $synonyms_stack .= "{" . trim(preg_replace('{\.$}', '', trim($json_prt->original)));


                        $i = 0;
                        foreach ($json_prt->paraphrases as $paraphrase) {

                            if ($i == 2) break;

                            $synonyms_stack .=  '|' . trim(preg_replace('{\.$}', '', trim($paraphrase->alt)));
                            $i++;
                        }

                        $synonyms_stack .= "}";
                        //$synonyms_stack = str_replace('.' ,  '' , $synonyms_stack);
                    }
                }

                if ($debug) {
                    echo '#####RETURNED SYNONYMS:::::::';
                    print_r($synonyms_stack);
                }


                $article = $this->find_remote_synonyms_and_replace($synonyms_stack, $article, true);


                // restoring ****
                $article = $this->restoreExecludes($article);


                // good we have the article found
                $this->article = $article;

                // now article contains the synonyms on the form {test|test2}
                return $this->update_post();
            }
        } // no email or password saved

        // failed to use wordai
        wp_auto_spinner_log_new('QuillBot Skip', 'We will use the internal synonyms database instead ');
        return $this->spin();
    }

    /*
	 * function wp_auto_spin_spin : spins an article by replacing synonyms from database treasure.dat
	 * @article: the article to be spinned
	 * return : the spinned article spinned or false if error.
	 */
    function spin()
    {
        // timer_start();

        // $opt = get_option('wp_auto_spin', array());
        /**
         * OPT_AUTO_SPIN_LINKS
         * OPT_AUTO_SPIN_URL_EX
         * OPT_AUTO_SPIN_TITLE_EX
         * OPT_AUTO_SPIN_CAP_EX
         * OPT_AUTO_SPIN_CAP_EX_TTL
         * OPT_AUTO_SPIN_CURLY_EX
         * OPT_AUTO_SPIN_NO_THESAURUS
         * OPT_AUTO_SPIN_ACTIVE_SHUFFLE
         */
        $opt = [];
        // $article = stripslashes($this->title) . '**9999**' . stripslashes($this->post);
        $article = stripslashes($this->post);

        if ($this->debug)
            echo "<br>" . time() . ": Post title is: " . $this->title;

        // match links
        $htmlurls = array();

        if (!in_array('OPT_AUTO_SPIN_LINKS', $opt)) {
            preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*?)<\/a>/s", $article, $matches, PREG_PATTERN_ORDER);
            $htmlurls = $matches[0];
        }

        // execlude urls pasted OPT_AUTO_SPIN_URL_EX
        $urls_txt = array();
        if (in_array('OPT_AUTO_SPIN_URL_EX', $opt)) {
            preg_match_all('/https?:\/\/[^<\s]+/', $article, $matches_urls_txt);
            $urls_txt = $matches_urls_txt[0];
        }

        // html tags
        preg_match_all("/<[^<>]+>/is", $article, $matches, PREG_PATTERN_ORDER);
        $htmlfounds = $matches[0];

        if ($this->debug)
            echo "<br>" . time() . ": HTML tags found: " . count($htmlfounds);

        // no spin items
        preg_match_all('{\[nospin\].*?\[/nospin\]}s', $article, $matches_ns);
        $nospin = $matches_ns[0];

        if ($this->debug)
            echo "<br>" . time() . ": no spins tags found: " . count($nospin);

        // extract all fucken shortcodes
        $pattern = "\[.*?\]";
        preg_match_all("/" . $pattern . "/s", $article, $matches2, PREG_PATTERN_ORDER);
        $shortcodes = $matches2[0];

        if ($this->debug)
            echo "<br>" . time() . ": shortcodes tags found: " . count($shortcodes);

        // javascript
        preg_match_all("/<script.*?<\/script>/is", $article, $matches3, PREG_PATTERN_ORDER);
        $js = $matches3[0];

        if ($this->debug)
            echo "<br>" . time() . ": js scripts found " . count($js);

        // numbers \d*
        /*
		 * preg_match_all ( '/\d{2,}/s', $article, $matches_nums );
		 * $nospin_nums = $matches_nums [0];
		 * sort ( $nospin_nums );
		 * $nospin_nums = array_reverse ( $nospin_nums );
		 */

        $nospin_nums = array();

        if ($this->debug)
            echo "<br>" . time() . ": no spins numbers found: " . count($nospin_nums);

        // extract all reserved words
        // $wp_auto_spinner_execlude = get_option('wp_auto_spinner_execlude', '');
        // $execlude = explode("\n", trim($wp_auto_spinner_execlude));
        $execlude = [];

        if ($this->debug)
            echo "<br>" . time() . ": Reserved words to exclude: " . count($execlude);

        // execlude title words
        // $autospin = get_option('wp_auto_spin', array());
        $autospin = [];
        if (in_array('OPT_AUTO_SPIN_TITLE_EX', $autospin)) {
            $extitle = explode(' ', $this->title);
            $execlude = array_merge($execlude, $extitle);

            if ($this->debug)
                echo "<br>" . time() . ": Title words to exclude: " . count($extitle);
        }

        // execlude capital letters
        $capped = array();
        if (in_array('OPT_AUTO_SPIN_CAP_EX', $opt) || in_array('OPT_AUTO_SPIN_CAP_EX_TTL', $opt)) {

            if (in_array('OPT_AUTO_SPIN_CAP_EX', $opt) && in_array('OPT_AUTO_SPIN_CAP_EX_TTL', $opt)) {
                $hystack_to_check = $article;
            } elseif (in_array('OPT_AUTO_SPIN_CAP_EX', $opt)) {
                $hystack_to_check = stripslashes($this->post);
            } else {
                $hystack_to_check = stripslashes($this->title);
            }


            // ececluding the capped words
            preg_match_all("{\b[A-Z][a-z']+\b}", $hystack_to_check, $matches_cap);

            $capped = $matches_cap[0];
            sort($capped);
            $capped = array_reverse($capped);

            if ($this->debug)
                echo "<br>" . time() . ": Capital words to exclude: " . count($capped);
        }

        // execlude curly quotes
        $curly_quote = array();
        if (in_array('OPT_AUTO_SPIN_CURLY_EX', $opt)) {

            // double smart qoute
            preg_match_all('{“.*?”}', $article, $matches_curly_txt);
            $curly_quote = $matches_curly_txt[0];

            // single smart quote
            preg_match_all('{‘.*?’}', $article, $matches_curly_txt_s);
            $single_curly_quote = $matches_curly_txt_s[0];

            // &quot;
            preg_match_all('{&quot;.*?&quot;}', $article, $matches_curly_txt_s_and);
            $single_curly_quote_and = $matches_curly_txt_s_and[0];

            // &#8220; &#8221;
            preg_match_all('{&#8220;.*?&#8221}', $article, $matches_curly_txt_s_and_num);
            $single_curly_quote_and_num = $matches_curly_txt_s_and_num[0];

            // regular duouble quotes
            $curly_quote_regular = array();
            if (in_array('OPT_AUTO_SPIN_CURLY_EX_R', $opt)) {
                preg_match_all('{".*?"}', $article, $matches_curly_txt_regular);
                $curly_quote_regular = $matches_curly_txt_regular[0];
            }

            $curly_quote = array_merge($curly_quote, $single_curly_quote, $single_curly_quote_and, $single_curly_quote_and_num, $curly_quote_regular);

            if ($this->debug)
                echo "<br>" . time() . ": curly quotes to exclude: " . count($curly_quote);
        }

        $exword_founds = array(); // ini

        foreach ($execlude as $exword) {

            if (preg_match('/\b' . preg_quote(trim($exword), '/') . '\b/u', $article)) {
                $exword_founds[] = trim($exword);
            }
        }

        // merge shortcodes to html which should be replaced
        // $htmlfounds = array_merge($nospin, $js, $htmlurls, $htmlfounds, $curly_quote, $urls_txt, $shortcodes, $nospin_nums, $capped);

        $htmlfounds = array_filter(array_unique($htmlfounds));

        // usort($htmlfounds, 'wp_auto_spinner_sort_by_length');

        if ($this->debug)
            echo "<br>" . time() . ": Total html founds to protect: " . count($htmlfounds);

        $i = 1;
        foreach ($htmlfounds as $htmlfound) {
            // $article = str_replace ( $htmlfound, '(' . str_repeat ( '*', $i ) . ')', $article );
            $article = str_replace($htmlfound, '(*' . $i . '*)', $article);
            $i++;
        }

        // replacing execluded words
        foreach ($exword_founds as $exword) {
            if (trim($exword) != '') {
                $article = preg_replace('/\b' . preg_quote(trim($exword), '/') . '\b/u', '(*' . $i . '*)', $article);
                $i++;
            }
        }

        // consequent protected quotes to reduce size of the text
        preg_match_all('!(?:\(\*\d*\*\)\s*)+!s', $article, $consequent_protect_tags);

        $consequent_protect_tags = $consequent_protect_tags[0];

        // sorting by large size
        // usort($consequent_protect_tags, 'wp_auto_spinner_sort_by_length');

        // replacing consequent protected
        $z = 0;
        foreach ($consequent_protect_tags as $consequent_protect_tag) {
            $article = str_replace($consequent_protect_tag, '(#' . $z . '#)', $article);
            $z++;
        }

        if ($this->debug)
            echo "<br>" . time() . ": Consequent protected tags: " . count($consequent_protect_tags);

        if ($this->debug) {
            echo "<br>" . time() . ": size of the article to replace on " . strlen($article);
            echo "<br>" . time() . ": loading thesaurus ";
        }
        // open the treasures db

        // $wp_auto_spinner_lang = get_option('wp_auto_spinner_lang', 'en');
        $wp_auto_spinner_lang = 'en';

        if (!in_array('OPT_AUTO_SPIN_NO_THESAURUS', $opt)) {

            // original synonyms
            $file = file(dirname(__FILE__) . '/treasures_' . $wp_auto_spinner_lang . '.dat');

            // deleted synonyms update
            // $deleted = array_unique(get_option('wp_auto_spinner_deleted_' . $wp_auto_spinner_lang, array()));
            $deleted = [];
            foreach ($deleted as $deleted_id) {
                unset($file[$deleted_id]);
            }

            // updated synonyms update
            // $modified = get_option('wp_auto_spinner_modified_' . $wp_auto_spinner_lang, array());
            $modified = [];

            foreach ($modified as $key => $val) {
                if (isset($file[$key])) {
                    $file[$key] = $val;
                }
            }
        } else {

            $file = array();
        }

        // custom synonyms on top of synonyms
        // $custom = get_option('wp_auto_spinner_custom_' . $wp_auto_spinner_lang, array());
        $custom = [];

        // usort($custom, 'wp_auto_spinner_sort_by_length');

        $file = array_merge($custom, $file);
        // echo $article;

        if ($this->debug)
            echo "<br>" . time() . ": loaded thesaurus ";

        // checking all words for existance
        foreach ($file as $line) {

            if ($this->debug)
                echo "<br>" . time() . ": processing line " . $line;

            // each synonym word
            $synonyms = explode('|', $line);
            $synonyms = array_map('trim', $synonyms);

            if (in_array('OPT_AUTO_SPIN_ACTIVE_SHUFFLE', $autospin)) {
                $synonyms2 = $synonyms;
            } else {
                $synonyms2 = array(
                    $synonyms[0]
                );
            }

            foreach ($synonyms2 as $word) {
                // echo ' word:'. $word;

                $word = str_replace('/', '\/', $word);
                if (trim($word) != '' & !in_array(strtolower($word), $execlude)) {

                    $word_without_first_char = $this->remove_first_char($word);

                    if (strpos($article, $word_without_first_char)) {

                        // skip number replacements
                        if (is_numeric($word))
                            continue;

                        // echo '..'.$word;
                        if (preg_match('/\b' . $word . '\b/u', $article)) {

                            // replace the word with it's hash str_replace(array("\n", "\r"), '',$line)and add it to the array for restoring to prevent duplicate

                            // restructure line to make the original word as the first word
                            $restruct = array(
                                $word
                            );
                            $restruct = array_merge($restruct, $synonyms);
                            $restruct = array_unique($restruct);
                            // $restruct=array_reverse($restruct);
                            $restruct = implode('|', $restruct);

                            $founds[md5($word)] = str_replace(array(
                                "\n",
                                "\r"
                            ), '', $restruct);

                            $md = md5($word);

                            if (is_numeric($word)) {
                                $article = preg_replace('/\b' . $word . ' \b/u', $md . ' ', $article);
                            } else {
                                $article = preg_replace('/\b' . $word . '\b/u', $md, $article);
                            }
                            // fix hivens like one-way
                            $article = str_replace('-' . $md, '-' . $word, $article);
                            $article = str_replace($md . '-', $word . '-', $article);
                        }

                        // replacing upper case words
                        $uword = $this->wp_auto_spinner_mb_ucfirst($word);

                        // echo ' uword:'.$uword;

                        if (preg_match('/\b' . $uword . '\b/u', $article)) {

                            $restruct = array(
                                $word
                            );
                            $restruct = array_merge($restruct, $synonyms);
                            $restruct = array_unique($restruct);
                            // $restruct=array_reverse($restruct);
                            $restruct = implode('|', $restruct);

                            $founds[md5($uword)] = $this->wp_auto_spinner_upper_case(str_replace(array(
                                "\n",
                                "\r"
                            ), '', $restruct));

                            if (is_numeric($word)) {
                                $article = preg_replace('/\b' . $uword . ' \b/u', md5($uword) . ' ', $article);
                            } else {
                                $article = preg_replace('/\b' . $uword . '\b/u', md5($uword), $article);
                            }
                        } // upper word check
                    } // word without first car exists somewhere
                }
            }
        } // foreach line of the synonyms file
        if ($this->debug)
            echo "<br>" . time() . ": Number of synonyms found: " . count($founds);

        // restore consequents
        $z = 0;
        foreach ($consequent_protect_tags as $consequent_protect_tag) {
            $article = str_replace('(#' . $z . '#)', $consequent_protect_tag, $article);
            $z++;
        }

        // restore html tags
        $i = 1;
        foreach ($htmlfounds as $htmlfound) {
            $article = str_replace('(*' . $i . '*)', $htmlfound, $article);
            $i++;
        }

        // replacing execluded words
        foreach ($exword_founds as $exword) {
            if (trim($exword) != '') {
                $article = str_replace('(*' . $i . '*)', $exword, $article);
                $i++;
            }
        }

        // replace hashes with synonyms
        if (isset($founds) && count($founds) != 0) {
            foreach ($founds as $key => $val) {
                $article = str_replace($key, '{' . $val . '}', $article);
            }
        }

        // deleting spin and nospin shortcodes
        $article = str_replace(array(
            '[nospin]',
            '[/nospin]'
        ), '', $article);
        return $article;

        // if ($this->debug) {
        //     echo "<br>" . time() . ':Spinning took ' . timer_stop() . ' seconds to complete ';
        // }
        // // now article contains the synonyms on the form {test|test2}
        // return $this->update_post();
    }

    // spintax post , update data , return array of data
    function update_post()
    {
        $spinned = $this->article;

        // synonyms
        if (stristr($spinned, '911911')) {
            $spinned = str_replace('911911', '**9999**', $spinned);
        }

        $spinned_arr = explode('**9999**', $spinned);

        $spinned_ttl = $spinned_arr[0];
        $spinned_cnt = $spinned_arr[1];

        // spintaxed wrirretten instance
        require_once('class.spintax.php');
        $spintax = new wp_auto_spinner_Spintax();
        $spintaxed = $spintax->spin($spinned);

        $spintaxed2 = $spintax->editor_form;

        $spintaxed_arr = explode('**9999**', $spintaxed);
        $spintaxed_arr2 = explode('**9999**', $spintaxed2);
        $spintaxed_ttl = $spintaxed_arr[0];
        $spintaxed_cnt = $spintaxed_arr[1];
        $spintaxed_cnt2 = $spintaxed_arr2[1];

        // update post meta
        $post_id = $this->id;
        update_post_meta($post_id, 'spinned_ttl', $spinned_ttl);
        update_post_meta($post_id, 'spinned_cnt', $spinned_cnt);
        update_post_meta($post_id, 'spintaxed_ttl', $spintaxed_ttl);
        update_post_meta($post_id, 'spintaxed_cnt', $spintaxed_cnt);
        update_post_meta($post_id, 'spintaxed_cnt2', $spintaxed_cnt2);
        update_post_meta($post_id, 'original_ttl', stripslashes($this->title));
        update_post_meta($post_id, 'original_cnt', stripslashes($this->post));

        $return = array();
        $return['spinned_ttl'] = $spinned_ttl;
        $return['spinned_cnt'] = $spinned_cnt;
        $return['spintaxed_ttl'] = $spintaxed_ttl;
        $return['spintaxed_cnt'] = $spintaxed_cnt;
        $return['spintaxed_cnt2'] = $spintaxed_cnt2;
        $return['original_ttl'] = $this->title;
        $return['original_cnt'] = $this->post;

        return $return;
    }

    // convert to upercase compatible with unicode chars
    function wp_auto_spinner_mb_ucfirst($string)
    {
        if (function_exists('mb_strtoupper')) {
            $encoding = "utf8";
            $firstChar = mb_substr($string, 0, 1, $encoding);
            $then = mb_substr($string, 1, mb_strlen($string), $encoding);
            return mb_strtoupper($firstChar, $encoding) . $then;
        } else {
            return ucfirst($string);
        }
    }

    /**
     * Remove the first char from the word
     *
     * @param unknown $string
     */
    function remove_first_char($string)
    {
        if (function_exists('mb_strtoupper')) {

            $encoding = "utf8";
            $then = mb_substr($string, 1, mb_strlen($string), $encoding);
        } else {
            $then = preg_replace('/^./u', '', $string);
        }

        if ($then !== '') {
            return $then;
        } else {
            return $string;
        }
    }

    // check the first letter of the word and upercase words in the line
    function wp_auto_spinner_upper_case($line)
    {
        $w_arr = explode('|', $line);

        for ($i = 0; $i < count($w_arr); $i++) {
            $w_arr[$i] = $this->wp_auto_spinner_mb_ucfirst($w_arr[$i]);
        }

        $line = implode('|', $w_arr);

        return $line;
    }

    /**
     * function replaceExecludes
     */
    function replaceExecludes($article, $opt)
    {

        // match links
        $htmlurls = array();

        if (!in_array('OPT_AUTO_SPIN_LINKS', $opt)) {
            preg_match_all("/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*?)<\/a>/s", $article, $matches, PREG_PATTERN_ORDER);
            $htmlurls = $matches[0];
        }

        // execlude urls pasted OPT_AUTO_SPIN_URL_EX
        $urls_txt = array();
        if (in_array('OPT_AUTO_SPIN_URL_EX', $opt)) {
            preg_match_all('/https?:\/\/[^<\s]+/', $article, $matches_urls_txt);
            $urls_txt = $matches_urls_txt[0];
        }

        // html tags
        preg_match_all("/<[^<>]+>/is", $article, $matches, PREG_PATTERN_ORDER);
        $htmlfounds = $matches[0];

        if ($this->debug)
            echo "<br>" . time() . ": HTML tags found: " . count($htmlfounds);

        // no spin items
        preg_match_all('{\[nospin\].*?\[/nospin\]}s', $article, $matches_ns);
        $nospin = $matches_ns[0];

        if ($this->debug)
            echo "<br>" . time() . ": no spins tags found: " . count($nospin);

        // extract all fucken shortcodes
        $pattern = "\[.*?\]";
        preg_match_all("/" . $pattern . "/s", $article, $matches2, PREG_PATTERN_ORDER);
        $shortcodes = $matches2[0];

        if ($this->debug)
            echo "<br>" . time() . ": shortcodes tags found: " . count($shortcodes);

        // javascript
        preg_match_all("/<script.*?<\/script>/is", $article, $matches3, PREG_PATTERN_ORDER);
        $js = $matches3[0];

        if ($this->debug)
            echo "<br>" . time() . ": js scripts found " . count($js);

        // numbers \d*
        /*
		 * preg_match_all ( '/\d{2,}/s', $article, $matches_nums );
		 * $nospin_nums = $matches_nums [0];
		 * sort ( $nospin_nums );
		 * $nospin_nums = array_reverse ( $nospin_nums );
		 */

        $nospin_nums = array();

        if ($this->debug)
            echo "<br>" . time() . ": no spins numbers found: " . count($nospin_nums);

        // extract all reserved words
        $wp_auto_spinner_execlude = get_option('wp_auto_spinner_execlude', '');
        $execlude = explode("\n", trim($wp_auto_spinner_execlude));

        if ($this->debug)
            echo "<br>" . time() . ": Reserved words to exclude: " . count($execlude);

        // execlude title words
        $autospin = get_option('wp_auto_spin', array());
        if (in_array('OPT_AUTO_SPIN_TITLE_EX', $autospin)) {
            $extitle = explode(' ', $this->title);
            $execlude = array_merge($execlude, $extitle);

            if ($this->debug)
                echo "<br>" . time() . ": Title words to exclude: " . count($extitle);
        }

        // execlude capital letters
        $capped = array();
        if (in_array('OPT_AUTO_SPIN_CAP_EX', $opt)) {
            // ececluding the capped words
            preg_match_all("{\b[A-Z][a-z']+\b}", $article, $matches_cap);

            $capped = $matches_cap[0];
            sort($capped);
            $capped = array_reverse($capped);

            if ($this->debug)
                echo "<br>" . time() . ": Capital words to exclude: " . count($capped);
        }

        // execlude curly quotes
        $curly_quote = array();
        if (in_array('OPT_AUTO_SPIN_CURLY_EX', $opt)) {

            // double smart qoute
            preg_match_all('{“.*?”}', $article, $matches_curly_txt);
            $curly_quote = $matches_curly_txt[0];

            // single smart quote
            preg_match_all('{‘.*?’}', $article, $matches_curly_txt_s);
            $single_curly_quote = $matches_curly_txt_s[0];

            // &quot;
            preg_match_all('{&quot;.*?&quot;}', $article, $matches_curly_txt_s_and);
            $single_curly_quote_and = $matches_curly_txt_s_and[0];

            // &#8220; &#8221;
            preg_match_all('{&#8220;.*?&#8221}', $article, $matches_curly_txt_s_and_num);
            $single_curly_quote_and_num = $matches_curly_txt_s_and_num[0];

            // regular duouble quotes
            $curly_quote_regular = array();
            if (in_array('OPT_AUTO_SPIN_CURLY_EX_R', $opt)) {
                preg_match_all('{".*?"}', $article, $matches_curly_txt_regular);
                $curly_quote_regular = $matches_curly_txt_regular[0];
            }

            $curly_quote = array_merge($curly_quote, $single_curly_quote, $single_curly_quote_and, $single_curly_quote_and_num, $curly_quote_regular);

            if ($this->debug)
                echo "<br>" . time() . ": curly quotes to exclude: " . count($curly_quote);
        }

        $exword_founds = array(); // ini

        foreach ($execlude as $exword) {

            if (preg_match('/\b' . preg_quote(trim($exword), '/') . '\b/u', $article)) {
                $exword_founds[] = trim($exword);
            }
        }

        // merge shortcodes to html which should be replaced
        $htmlfounds = array_merge($nospin, $js, $htmlurls, $htmlfounds, $curly_quote, $urls_txt, $shortcodes, $nospin_nums, $capped);

        if ($this->debug)
            echo "<br>" . time() . ": Total number of founds to protect: " . count($htmlfounds);

        $htmlfounds = array_filter(array_unique($htmlfounds));

        usort($htmlfounds, 'wp_auto_spinner_sort_by_length');

        if ($this->debug)
            echo "<br>" . time() . ": Total UNIQUE founds to protect: " . count($htmlfounds);

        $i = 1;
        foreach ($htmlfounds as $htmlfound) {
            // $article = str_replace ( $htmlfound, '(' . str_repeat ( '*', $i ) . ')', $article );
            $article = str_replace($htmlfound, '(*' . $i . '*)', $article);
            $i++;
        }

        // replacing execluded words
        foreach ($exword_founds as $exword) {
            if (trim($exword) != '') {
                $article = preg_replace('/\b' . preg_quote(trim($exword), '/') . '\b/u', '(*' . $i . '*)', $article);
                $i++;
            }
        }

        if ($this->debug)
            echo "<br><br>" . time() . ": Article after protect and before consequent:<hr> " . $article;

        // consequent protected quotes to reduce size of the text
        preg_match_all('!(?:\(\*\d*\*\)\s*){2,}!s', $article, $consequent_protect_tags);


        $consequent_protect_tags = array_map('trim', $consequent_protect_tags[0]);

        // sorting by large size
        usort($consequent_protect_tags, 'wp_auto_spinner_sort_by_length');

        // replacing consequent protected
        $z = 0;
        foreach ($consequent_protect_tags as $consequent_protect_tag) {
            $article = str_replace($consequent_protect_tag, '(#' . $z . '#)', $article);
            $z++;
        }

        if ($this->debug) {
            echo "<br>" . time() . ": Consequent protected tags: " . count($consequent_protect_tags);
            echo  "<br><br>" . time() . "Article after consequent :-<hr>" . $article;
        }

        // eg gathered the data.(#1190#)I asked (*6*)my friends on Twitter(*276*):(#7#)The data is stunning
        // get all protected numbered tags like (*6*) and (#1190#)
        preg_match_all('!\([\*|#]\d+[\*|#]\)!s', $article, $all_protected_tags);


        if ($this->debug) {
            echo "<br>" . time() . ":Final all protected tags count " .  count($all_protected_tags[0]);
        }

        //replace all protected tags by (*)
        //gathered the data.(*)I asked (*)my friends on Twitter(*):(*)The data is stunning
        $article = preg_replace('!\([\*|#]\d+[\*|#]\)!s', '(*)', $article);


        if ($this->debug) {
            echo "<br>" . time() . ": Size of the article to replace on " . strlen($article);

            echo  "<br><br>" . time() . "Article before starting rewriting :-<hr>" . $article;
        }



        // save the exwords so we can restore
        $this->htmlfounds = $htmlfounds;
        $this->execludewords = $exword_founds;
        $this->consequent_protect_tags = $consequent_protect_tags;
        $this->all_protected_tags = $all_protected_tags[0];

        return $article;
    }

    /**
     * function:restore execludes astrics to real content
     */
    function restoreExecludes($article)
    {

        $htmlfounds = $this->htmlfounds;
        $exword_founds = $this->execludewords;
        $all_protected_tags = $this->all_protected_tags;

        //all protected restore (*) to (*1*) , (#2#)
        $num = 1;
        foreach ($all_protected_tags as $single_protected_tag) {
            $article = preg_replace('{\(\*\)}s', $single_protected_tag, $article, 1);
        }

        //repalce consequents
        $z = 0;
        foreach ($this->consequent_protect_tags as $single_consequent_tag) {
            $article = str_replace("(#$z#)",  $single_consequent_tag, $article);
            $z++;
        }

        if ($this->debug) {

            echo  "<br><br>" . time() . "Article after conseqent replacement :-<hr>" . $article;
        }

        // restore html tags
        $i = 1;
        foreach ($htmlfounds as $htmlfound) {
            $article = str_replace('(*' .  $i . '*)', $htmlfound, $article);
            $i++;
        }

        // replacing execluded words
        foreach ($exword_founds as $exword) {
            if (trim($exword) != '') {
                $article = str_replace('(*' .  $i . '*)', $exword, $article);
                $i++;
            }
        }

        // deleting spin and nospin shortcodes
        $article = str_replace(array(
            '[nospin]',
            '[/nospin]'
        ), '', $article);

        return $article;
    }

    /**
     * Extract returned synonyms and replace them in the original article
     *
     * @param string $remote_synonyms
     * @param string $article
     * @param boolean no_words: if true, it will not use /b modifiers for REGEX
     */
    function find_remote_synonyms_and_replace($remote_synonyms, $article, $no_words = false)
    {

        // extract all synonyms
        preg_match_all('/{[^{]*?}/', $remote_synonyms, $syns_matchs);

        $synonyms = $syns_matchs[0];

        $article_first_part = '';


        // found synonyms count flag
        $processed_synonyms_count = 0;

        // replaced synonyms count flag
        $replaced_synonyms_count = 0;

        // preg replace count flag init
        $preg_replace_count = 0;

        foreach ($synonyms as $synonym_key => $synonym_val) {

            if (stristr($synonym_val, '|') && !stristr($synonym_val, '*')) {
                $synonym_val_clean = str_replace('{', '', $synonym_val);
                $synonym_val_clean = str_replace('}', '', $synonym_val_clean);

                $synonym_val_parts = explode('|', $synonym_val_clean);


                // reset preg replace count flag
                $preg_replace_count = 0;

                if ($no_words) {
                    $article = preg_replace('{' . preg_quote($synonym_val_parts[0]) . '}u', '{' . $synonym_key . '}', $article, 1, $preg_replace_count);
                } else {
                    $article = preg_replace('{\b' . preg_quote($synonym_val_parts[0]) . '\b}u', '{' . $synonym_key . '}', $article, 1, $preg_replace_count);
                }

                // if the synonym was replaced in the article then increment the replaced synonyms count
                if ($preg_replace_count > 0) {
                    $replaced_synonyms_count++;
                }

                if (stristr($article, '{' . $synonym_key . '}')) {
                    $article_parts = explode('{' . $synonym_key . '}', $article);
                    $article_first_part .= $article_parts[0] . '{' . $synonym_key . '}';
                    $article = $article_parts[1];
                }

                // increment processed synonyms count
                $processed_synonyms_count++;
            }
        }

        $article = $article_first_part . $article;

        // restoring syns
        foreach ($synonyms as $synonym_key => $synonym_val) {
            $article = str_replace('{' . $synonym_key . '}', $synonym_val, $article);
        }

        // log the number of processed synonyms
        wp_auto_spinner_log_new('Found/Processed synonyms', 'Number of found synonyms set: (' . $processed_synonyms_count . ') and processed (' . $replaced_synonyms_count . ')');

        return $article;
    }
    /**
     * convert headers to array
     *
     * @param unknown $header_text
     * @return unknown[]
     */
    function putHeadersTextIntoArray($header_text)
    {
        $headers = array();
        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        return $headers;
    }

    /**
     * function to compress the article by replacing <picutre.*?picture> and <img.*?img> with a sample image <img src="1.jpg" />
     */
    function openai_compress_article($article)
    {

        //match all <pictures
        preg_match_all('/<picture.*?picture>/is', $article, $matches, PREG_PATTERN_ORDER);

        //get the matches
        $pictures = $matches[0];

        //match all <img
        preg_match_all('/<img.*?img>/is', $article, $matches, PREG_PATTERN_ORDER);

        //get the matches
        $imgs = $matches[0];

        //merge the pictures and imgs
        $all_images = array_merge($pictures, $imgs);

        //get the unique images
        $all_images = array_unique($all_images);

        //loop through the images
        $i = 0;
        foreach ($all_images as $image) {

            //replace the image with a sample image
            $article = str_replace($image, '<img src="' . $i . '.jpg" />', $article);

            //increment the counter
            $i++;
        }

        //save a copy of the array in $this->htmlfound
        $this->htmlfounds = $all_images;

        //return the article
        return $article;
    }

    /**
     * function to restore the images
     */
    function openai_restore_images($article)
    {

        //get the images
        $all_images = $this->htmlfounds;

        //loop through the images
        $i = 0;
        foreach ($all_images as $image) {

            //replace the image with a sample image
            $article = str_replace('<img src="' . $i . '.jpg" />', $image, $article);

            //increment the counter
            $i++;
        }

        //return the article
        return $article;
    }

    /**
     * post request to get the content from the plugin API
     * extreme test case non latin https://pastebin.com/e4mdadJz
     * 
     */
    function api_call($function, $args)
    {

        // api url
        $api_url = 'http://api.valvepress.com/api/' . $function;


        // license check
        $wp_auto_spinner_license_active = get_option('wp_auto_spinner_license_active', '');

        // if not active throw error
        if (trim($wp_auto_spinner_license_active) == '') {

            // not active, throw error
            throw new Exception('License not active, please activate your license to use this feature');
        }

        // get the license key
        $wp_auto_spinner_license_key = get_option('wp_auto_spinner_license', '');
        $wp_auto_spinner_license_key = trim($wp_auto_spinner_license_key);

        //add license to args array
        $args['license'] = $wp_auto_spinner_license_key;

        //add domain name to args array
        $args['domain'] = $_SERVER['HTTP_HOST'];

        //issue:23670 domain sent as null
        //solution: check if domain is null, if null then get the domain from the site url
        if ($args['domain'] == null || $args['domain'] == '') {
            $site_url = site_url();

            //get the domain name from the site url
            $domain = parse_url($site_url, PHP_URL_HOST);

            //add the domain to the args array

            $args['domain'] = $domain;
        }

        //json creating
        $json = json_encode($args);

        //save a json copy to a custom field
        update_post_meta($this->id, 'wp_auto_spinner_json', $json);

        //init curl
        $ch = curl_init();

        //POST args to api url using curl and $this->ch as the handle		 
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 180);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        //post json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $server_output = curl_exec($ch);

        //check if curl error
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        //curl info
        $curl_info = curl_getinfo($ch);

        //close curl
        curl_close($ch);

        //wrap in try catch	
        try {
            $server_output = json_decode($server_output, true);
        } catch (Exception $e) {
            throw new Exception('Error decoding server output');
        }

        //check if server output is json and has error and the error contains "Rate limit reached", wait for 20 seconds and retry 
        if (is_array($server_output) && isset($server_output['error']) && strpos($server_output['error'], 'Rate limit reached') !== false) {

            //log the error
            wp_auto_spinner_log_new('Rate limit reached', 'Rate limit reached, waiting for 20 seconds and retrying');

            sleep(20);
            return $this->api_call($function, $args);
        }

        //check if server output is json and has error
        if (is_array($server_output) && isset($server_output['error'])) {
            throw new Exception($server_output['error']);
        }

        //check if server output is json
        if (is_array($server_output) && isset($server_output['result'])) {
            return $server_output['result'];
        }

        //check if server output is not json
        if (!is_array($server_output)) {

            //save output to a custom field 
            update_post_meta($this->id, 'wp_auto_spinner_server_output', $server_output);


            throw new Exception('Server output is not json');
        }



        return $server_output['result'];
    }

    /**
     * Function takes two arrays, find leading and trailing spaces in the first array and add them to the second array
     */
    function add_leading_trailing_spaces($array1, $array2)
    {

        // loop through the first array
        foreach ($array1 as $key => $value) {

            // if the value is not empty
            if (!empty($value)) {

                // get the leading spaces using regex
                preg_match('/^(\s*)/', $value, $matches);
                $leading_spaces = $matches[1];

                // get the trailing spaces using regex
                preg_match('/(\s*)$/', $value, $matches);
                $trailing_spaces = $matches[1];


                // add the leading spaces to the second array
                $array2[$key] = $leading_spaces . $array2[$key];

                // add the trailing spaces to the second array
                $array2[$key] = $array2[$key] . $trailing_spaces;
            }
        }

        return $array2;
    }
}//end class 