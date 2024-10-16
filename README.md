# chatgpt-web-free
# 功能说明

该项目实现了一个简单的ChatGPT聊天系统，上传普通PHP环境即可使用，无需数据库，所有数据仅在本地浏览器存储。支持主流OpenAI调用格式。用户可以创建和切换多个会话，并与助手进行交流。**如果对您有帮助可以加群交流**

**以下是主要功能的详细介绍：**
## 商业版
我们提供了更多商业版本供您选择，详情访问：[https://mall.foxlet.cn/](https://mall.foxlet.cn/)
免费AI使用地址：[https://www.aifoxtech.com/](https://www.aifoxtech.com/)
## 功能概述

1. **会话管理**
    - 用户可以创建多个会话。
    - 用户可以在不同的会话之间切换。
    - 会话列表在页面左侧展示，并可以显示每个会话的名称。
    - 自定义会话名称便于区分。
    - md代码解析高亮。
    - 更多......。

2. **消息交流**
    - 用户可以输入消息并发送给助手。
    - 历史消息会在聊天区域中显示出来，包括用户和助手的消息。
    - 用户消息和助手消息采用不同的样式，便于区分。

3. **用户界面**
    - 聊天区域分为会话列表、聊天窗口和输入区域。
    - 用户可以在顶部输入框中编辑当前会话的名称。

4. **其他功能**
    - 支持 Markdown 语法，在消息中可以显示格式化文本。
    - 支持代码高亮和代码块复制功能。
    - 实时显示助手正在输入的反馈（小旋转标志）。


## 主要部分代码说明


### 会话列表

- 通过 `PHP` 会话管理器保存多个对话。
- 在用户创建新的会话时，自动生成新的会话 ID。
- 当前会话通过 URL 参数 `conversation_id` 管理，并存储在 `PHP` 会话变量中。


### 消息发送与接收

- 用户输入通过 `fetch` 函数发送到后台 `chat.php` 文件，进行智能回复处理。
- 使用 OpenAI API 进行助手回复，通过流式传输实现助手实时回应。
- 助手回复的消息会动态插入到聊天窗口中。


### 界面组件

- 使用 `Bootstrap` 和自定义 `CSS` 样式实现页面布局和视觉效果。
- `jQuery` 用于前端交互处理、消息的发送与接收。
- 使用 `marked.js` 和 `highlight.js` 实现 Markdown 渲染和代码高亮显示。
- `Clipboard.js` 用于实现代码块复制功能。


### 主要代码结构


#### `index.php`

- 初始化和管理会话的主要逻辑。
- 会话的增删改查以及前端界面设计。


#### `chat.php`

- 新建会话功能。
- 用户消息的处理与助手回复的获取。
- 会话历史的管理以及与 OpenAI API 的交互。
- 设置API密钥$api_key = 'sk-****'; // 请替换为您的实际 API 密钥，**默认OpenAI接口接入的是智创聚合API平台[https://s.lconai.com/](https://s.lconai.com/)，所以密钥请前往该平台注册生成，也可以自己更改其它平台**
- 关于模型，默认是gpt-4o-all模型，可根据自己需求自行更改'model' => 的值。

## 技术栈

- 前端：`HTML`, `CSS`, `JavaScript`, `Bootstrap`, `jQuery`
- 后端：`PHP`
- 第三方库：
    - `marked.js`（用于 Markdown 解析）
    - `highlight.js`（用于代码高亮显示）
    - `Clipboard.js`（用于复制功能）
    - `OpenAI API`（用于实现智能助手）


## 使用说明

1. **创建新会话**
    在会话列表中点击 "新建对话" 按钮，会在左侧添加新的会话。

2. **切换会话**
    点击左侧会话列表中的任一会话名称即可切换到相应会话。

3. **编辑会话名称**
    在聊天窗口顶部输入新的会话名称，点击 "保存" 按钮，当前会话名称会被更新。

4. **发送消息**
    在输入框中输入消息，点击 "发送" 按钮或按下 `Enter` 键（适用于单行输入）发送消息。

5. **复制代码块**
    在消息中高亮的代码块右上角点击复制按钮即可复制代码到剪贴板。


## 示例页面
![image](https://github.com/user-attachments/assets/12fc983f-605d-415f-8ad3-adfd606c825b)
![image](https://github.com/user-attachments/assets/e4245318-41ae-4fab-a7f0-be449e96ad62)

## 添加好友加群
![个人微信(1)](https://github.com/user-attachments/assets/4ba5b9e5-d1d2-4d23-8df1-08651e68e1cd)


## 关注我们
![image](https://github.com/user-attachments/assets/e97927f7-d7af-4d91-85e0-9089482b874b)



