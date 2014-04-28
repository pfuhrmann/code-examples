'use strict';

var request = require('request'),
    cheerio = require('cheerio'),
    mongoose = require('mongoose'),
    URLModel = mongoose.model('Url'),
    async = require('async'),
    _ = require('lodash');

/*
* Check if giveb URL is unique within DB
*/
var checkUniqueURL = function(url, callback) {
    var niceURL = url;

    // Checking this is unique url
    URLModel.findOne({ url : niceURL }, function(err, URL){
        // URL not unique
        if (URL) {
            callback(null, URL);
        // Unique
        } else {
            callback(null, false);
        }
    });
};

/*
* Convert 'ugly' URL to 'pretty' one
*/
function prettyURL(urlUgly) {
    if (!urlUgly) {
        return false;
    }

    var HOST = 'http://www.rightmove.co.uk';
    var splitURL = urlUgly.split(';'); // Removing session part
    var splitURL2 = splitURL[0].split('/'); // Separate slug
    var priority = 3;
    var niceURL = '';

    // Only root adresses
    if (splitURL2[0] === '') {
        // Building up nice URL
        if (splitURL2[1] === 'svr') {
            niceURL = ''; // We don't need that
        } else if (splitURL2[2] === 'svr') {
            niceURL = HOST+'/'+splitURL2[1]+'';
        } else if (splitURL2[3] === 'svr') {
            niceURL = HOST+'/'+splitURL2[1]+'/'+splitURL2[2];
        } else if (splitURL2[4] === 'svr') {
            niceURL = HOST+'/'+splitURL2[1]+'/'+splitURL2[2]+'/'+splitURL2[3];
        } else if (splitURL2[5] === 'svr') {
            niceURL = HOST+'/'+splitURL2[1]+'/'+splitURL2[2]+'/'+splitURL2[3]+'/'+splitURL2[4];
        } else {
            niceURL = HOST+splitURL[0];
        }

        // Deciding on the priority based on the URL
        switch(splitURL2[1])
        {
            case 'estate-agents':
            case 'property-for-sale':
            case 'property-to-rent':
                priority = 1;
                break;
            case 'house-prices':
                priority = 2;
                break;
        }
    }

    return {
        'url' : niceURL,
        'priority' : priority
    };
}

function extractURLs(html, URLcount, parsedURLs, uniqueURLs, callback) {
    // Load HTML code
    var $ = cheerio.load(html),
        a = $('a'),
        j = 0;

    a.each(function(/*i*/) {
        var urlUgly = $(this).attr('href'); // Getting URL from a.href
        var niceURL = prettyURL(urlUgly); // Convert to nice URL

        if (!urlUgly || !niceURL.url) {
            // Check if last URL
            if (j === a.length-1) {
                callback();
            }
            j++;
        } else {     
            console.log('Found! | URL='+niceURL.url); 
            
            // Check if URL is unique within current DB
            checkUniqueURL(niceURL.url, function(err, URL) {
                // Not unique URL so we will update 
                // URL object details
                if (URL) {
                    // Update parent count
                    var urlDoc = _.extend(URL, {
                        urlsParent : URL.urlsParent + 1
                    });

                    // Save updated found URL object persistently
                    urlDoc.save(function(err) {
                        if (err) {
                            console.log('Error saving URL='+urlDoc.url); 
                            console.log('Error='+err); // Log error
                        } else {
                            console.log('Duplicate. URL data updated successfully.');//+urlDoc);
                        }
                    });
                // This is NEW URL    
                } else {
                    // Checking if URL us unique withing current round
                    // - no need to store 
                    if(uniqueURLs.indexOf(niceURL.url) === -1) {
                        uniqueURLs.push(niceURL.url);
                        parsedURLs.push(niceURL);
                        console.log('New URL found! URL-nice='+niceURL.url);
                    } else {
                        console.log('Duplicate URL found in parsedURLs.');
                    }
                }

                // Increase count for statistics
                URLcount++;
                // Check if last URL
                if (j === a.length-1) {
                    callback();
                }
                j++;
            });
        }
    });
}

/*
* Go through saved URLs and scrap the property data
* HTTP GET /scraper-property
*/
exports.scraperURL = function(req, res) {

    // Get unvisited URLs from DB
    URLModel.find({ lastVisited : null })
    .sort({'created': -1})
    .limit(10000)
    .exec(function traverseURLs(err, URLs) {
        // Check for errors in querying Mongo     
        if (err) {
            // Log error
            console.log(err);
        } else {
            console.log('URL received: URL='+URLs);   
        }

        // Loop through each URL in the Mongo collection
        //  than extract all URLS within that URL
        var uniqueURLs = []; // Array to store all parsed URLs within this run
        var parsedURLs = []; // Array to store all parsed URLs within this run
        async.eachLimit(URLs, 1, function scrapURLTask(urlDoc, callback) {
            var URLcount = 0; // Counting URLs within this URL
            async.series([
                function doRequest(callback) {
                    // Actual request to the server
                    request(urlDoc.url, function afterRequest(error, response, html) {
                        if (error && response.statusCode !== 200) {
                            // Log Request error
                            console.log('Error requesting url='+urlDoc.url);
                            console.log('Error='+error);
                            callback('Error requesting URL');
                        } else {
                            extractURLs(html, URLcount, parsedURLs, uniqueURLs, function(err) {
                                // Handle error
                                if (err) {
                                    callback(err);
                                }
                                callback();
                            });
                        }
                    });
                }
            ], function updateCurrentURL(err) { // All URLs are scraped
                // Handle error
                if (err) {
                    callback(err);
                }

                // Update current URL object with fresh scraping data
                urlDoc = _.extend(urlDoc, {
                    lastVisited : new Date(),
                    numVisited : urlDoc.numVisited + 1,
                    urlsWithin : URLcount
                });

                // Save URL object persistently
                urlDoc.save(function(err) {
                    if (err) {
                        console.log('==> Error saving URL='+urlDoc.url); 
                        console.log('==> Error='+err); // Log error
                        callback(err);
                    } else {
                        console.log('==> Current URL model data updated successfully='+urlDoc);
                    }
                    callback();
                });
            });
        // All, done we are going to save all the URLs 
        //  to the persistent storage
        }, function saveParsedURLs(err) {
            // There was some error in the execution stack
            if (err) {
                 console.log('ERROR! '+err);
            }
            // Save URLs to extract
            console.log('-------- DONE EXTRACTING --------');
            console.log('Unique URLs found: '+parsedURLs.length);
            parsedURLs.forEach(function saveNewURL(url) {
                // Create new URL mongo model
                var urlModel = new URLModel({
                    'url' : url.url,
                    'priority' : url.priority,
                });

                // Save the model
                urlModel.save(function(err) {
                    console.log('Storing data for url='+url.url);
                    if (err) { // Log error
                        if (err.code === 11000) {// Duplicate key Mongo error
                            console.log('Duplicate url found!'); 
                        } else {
                            console.log('Error storing new URL! '+err); 
                        }
                    } else {
                        console.log('Stored!');
                    }
                    console.log('...');
                });
            });

            // Send data to the view
            res.render('scraper', {
                scraperData: parsedURLs
            });
        });
    });
};