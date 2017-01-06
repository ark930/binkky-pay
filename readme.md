# Binkky Payment API

第三方支付聚合API

## 适用对象

- Binkky Merchat API
- 第三方开发者

## 技术架构

- PHP 7.0 / Lumen 5.3
- MySQL 5.7
- Redis 3.x
- ElasticSearch 5.x

## 功能

## API

### Authentication 认证

### Errors 错误处理
使用 HTTP 状态码来表明一个 API 请求的成功或失败。通常，返回值 2xx 表明 API 请求成功。
返回值 4xx 表明 API 请求时被提供了错误的信息（比如参数缺失，参数错误，支付渠道错误等）。
返回值 5xx 表明 API 请求时，Ping++ 服务器发生了错误。
###### 属性 ######
参数 | 描述
---- | ----
type | 错误类型，可以是`invalid_request_error`、`api_error`或是`channel_error`。
message | 返回具体的错误描述。
code | **optional** 错误码，因为第三方支付渠道返回的错误代码。
param | **optional** 当发生参数错误时返回具体的参数名，如`id`。

### HTTP 状态码 ###
200 OK - 一切正常。  
400 Bad Request - 请求失败。  
401 Unauthorized - 接口未授权。  
402 Request Failed - 参数格式正确但是请求失败，一般由业务错误引起。
404 Not Found - 请求的资源不存在。  
422 Unprocessable Entity - 参数验证失败。  
429 - Too Many Requests
5xx Server errors - 服务器内部错误。  
