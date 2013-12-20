%{function=\"customTypeQuery(\"K2ItemDetail\",$refId,\"$.introText\")\"}%
%{function=\"customTypeQuery(\"K2CategoryInfo\",$groupId,\"$.items[0].params\",\"$.itemAuthor\")\"}%

%{function=\"assertInsert($ownerRefStr,customTypeQuery(\"K2CategoryInfo\",$groupId,\"$.items[0].params\",\"$.itemAuthor\"))\"}%

%{function=\"xapp_json_query($params,\"$.catTitle\")\"}%
%{function=\"xapp_json_query($params,\"$.catDescription\")\"}%


%{function=\"assertInsert($title,xapp_json_query($params,\"$.catTitle\")\"}%
%{function=\"assertInsert(htmlMobile($description),xapp_json_query($params,\"$.catDescription\"))\"}%

%{function=\"assertInsert($title,xapp_json_query($params,\"$.catTitle\"))\"}%

asdasd
