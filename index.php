<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/
/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service. 
# Blob storage stores unstructured data such as text, binary data, documents or media files. 
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS. 
#
# Documentation References: 
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php 
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/ 
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx 
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx 
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/ 
#
**/

require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString="DefaultEndpointsProtocol=https;AccountName=agiswebapp;AccountKey=ttNexieWL2Ne3xsOtdj0d5+YKks7bZiJOn6NL8bTGYaHMlTBDK13xZhR95feJYE1hVpBq0KmdcUsxJ/x0c7hgg==";

// Create blob client.
$blobClient=BlobRestProxy::createBlobService($connectionString);

$containerName="blockblobs".generateRandomString();


if (isset($_GET["Submit"])) {
    $fileToUpload=strtolower($_FILES["image"]["name"]);

    // Create container options object.
    $createContainerOptions=new CreateContainerOptions();

    // Set public access policy. Possible values are
    // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
    // CONTAINER_AND_BLOBS:
    // Specifies full public read access for container and blob data.
    // proxys can enumerate blobs within the container via anonymous
    // request, but cannot enumerate containers within the storage account.
    //
    // BLOBS_ONLY:
    // Specifies public read access for blobs. Blob data within this
    // container can be read via anonymous request, but container data is not
    // available. proxys cannot enumerate blobs within the container via
    // anonymous request.
    // If this value is not specified in the request, container data is
    // private to the account owner.
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");


    try {
        // Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);

        # Upload file as a block blob echo "Uploading BlockBlob: ".PHP_EOL;
        echo $fileToUpload;
        echo "<br />";

        $content=fopen($_FILES["image"]["tmp_name"], "r");

        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

        header("Location: index.php");

    }

    catch(ServiceException $e) {
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code=$e->getCode();
        $error_message=$e->getMessage();
        echo $code.": ".$error_message."<br />";
    }

    catch(InvalidArgumentTypeException $e) {
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code=$e->getCode();
        $error_message=$e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

else if(isset($_GET["Cleanup"])) {
    try {
        $blobClient->deleteContainer($_GET["containerName"]);
        header("Location: index.php");

    }

    catch(ServiceException $e) {
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code=$e->getCode();
        $error_message=$e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

?>
<!DOCTYPE html>
<html>

    <head>
        <title>Analyze Sample</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
            integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
            integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
        </script>
    </head>

    <body>

        <script type="text/javascript">
            function processImage(url) {
                // **********************************************
                // *** Update or verify the following values. ***
                // **********************************************

                // Replace <Subscription Key> with your valid subscription key.
                var subscriptionKey = "23b8f26fd9d445e78f28b684c656dfbe";

                // You must use the same Azure region in your REST API method as you used to
                // get your subscription keys. For example, if you got your subscription keys
                // from the West US region, replace "westcentralus" in the URL
                // below with "westus".
                //
                // Free trial subscription keys are generated in the "westus" region.
                // If you use a free trial subscription key, you shouldn't need to change
                // this region.
                var uriBase = "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
                // "https://southeastasia.api.cognitive.microsoft.com/";

                // Request parameters.
                var params = {
                    "visualFeatures": "Categories,Description,Color",
                    "language": "en",
                }

                ;

                // Display the image.
                var sourceImageUrl = url;
                document.querySelector('#sourceImage').setAttribute('src', sourceImageUrl);

                console.log(url);

                // Make the REST API call.
                $.ajax({
                        url: uriBase + "?" + $.param(params),

                        // Request headers.
                        beforeSend: function (xhrObj) {
                                xhrObj.setRequestHeader("Content-Type", "application/json");
                                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key", subscriptionKey);
                            }

                            ,

                        type: "POST",

                        // Request body.
                        data: '{"url": ' + '"' + sourceImageUrl + '"}',
                    }

                ).done(function (data) {
                        // Show formatted JSON on webpage.
                        $("#responseTextArea").val(JSON.stringify(data, null, 2));
                        $("#caption").text(data.description.captions[0].text);
                    }

                ).fail(function (jqXHR, textStatus, errorThrown) {
                        // Display error message.
                        var errorString = (errorThrown === "") ? "Error. " : errorThrown + " (" + jqXHR.status +
                            "): ";
                        errorString += (jqXHR.responseText === "") ? "" : jQuery.parseJSON(jqXHR.responseText)
                            .message;
                        alert(errorString);
                    }

                );
            }

            ;

        </script>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2>Compute Vision with Blob Storage</h2>
                    <form method="post" action="index.php?Submit" enctype="multipart/form-data">
                        <div class="form-group"><label for="image">File Image</label><input type="file"
                                class="form-control-file" id="image" name="image"></div><button type="submit"
                            class="btn btn-primary">Upload Image</button>
                    </form>
                    <script></script><br><br>
                    <div id="wrapper" style="width:1020px; display:table;">
                        <div id="jsonOutput" style="width:600px; display:table-cell;">Response: <br><br><textarea
                                id="responseTextArea" class="UIInput" style="width:580px; height:400px;"></textarea>
                        </div>
                        <div id="imageDiv" style="width:420px; display:table-cell;">Source image: <br><br><img
                                id="sourceImage" width="400" src="" /></div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            Caption:<br>
                            <div id="caption"></div>
                        </div>
                    </div>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th scope='col'>Name</th>
                                <th scope='col'>URL</th>
                                <th scope='col'>View</th>
                                <th scope='col'>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                         $listBlobsOptions=new ListBlobsOptions();
$blobContainers=$blobClient->listContainers();
$blobContainerArray=$blobContainers->getContainers();

$firstContaier = $blobContainerArray[0]->getName();
$firstBlob = $blobClient->listBlobs($firstContaier, $listBlobsOptions);

$firstUrl = $firstBlob->getBlobs()[0]->getUrl();

?>
                            <script>
                                processImage('<?=$firstUrl;?>')

                            </script>
                            <?php

foreach($blobContainerArray as $container) {
    $result=$blobClient->listBlobs($container->getName(), $listBlobsOptions);

    foreach ($result->getBlobs() as $blob) {
        ?><tr>
                                <th><?=$blob->getName()?></th>
                                <td><?=$blob->getUrl()?></td>
                                <td><button type="button" class="btn btn-primary"
                                        onClick="processImage('<?=$blob->getUrl();?>')">View</button></td>
                                <td>
                                    <form method="post"
                                        action="index.php?Cleanup&containerName=<?php echo $container->getName(); ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button></form>
                                </td>
                            </tr><?php
    }
}

?></tbody>
                    </table>
    </body>
    </div>
    </div>
    </div>

</html>
