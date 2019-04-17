/** 
 * 时间戳格式化函数 
 * @param  {string} format    格式 
 * @param  {int}    timestamp 要格式化的时间 默认为当前时间 
 * @return {string}           格式化的时间字符串 
 */
function date(format, timestamp){  
    var a, jsdate=((timestamp) ? new Date(timestamp*1000) : new Date()); 
    var pad = function(n, c){ 
        if((n = n + "").length < c){ 
            return new Array(++c - n.length).join("0") + n; 
        } else { 
            return n; 
        } 
    }; 
    var txt_weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]; 
    var txt_ordin = {1:"st", 2:"nd", 3:"rd", 21:"st", 22:"nd", 23:"rd", 31:"st"}; 
    var txt_months = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];  
    var f = { 
        // Day 
        d: function(){return pad(f.j(), 2)}, 
        D: function(){return f.l().substr(0,3)}, 
        j: function(){return jsdate.getDate()}, 
        l: function(){return txt_weekdays[f.w()]}, 
        N: function(){return f.w() + 1}, 
        S: function(){return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th'}, 
        w: function(){return jsdate.getDay()}, 
        z: function(){return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0}, 
        
        // Week 
        W: function(){ 
            var a = f.z(), b = 364 + f.L() - a; 
            var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1; 
            if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){ 
                return 1; 
            } else{ 
                if(a <= 2 && nd >= 4 && a >= (6 - nd)){ 
                    nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31"); 
                    return date("W", Math.round(nd2.getTime()/1000)); 
                } else{ 
                    return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0); 
                } 
            } 
        }, 
        
        // Month 
        F: function(){return txt_months[f.n()]}, 
        m: function(){return pad(f.n(), 2)}, 
        M: function(){return f.F().substr(0,3)}, 
        n: function(){return jsdate.getMonth() + 1}, 
        t: function(){ 
            var n; 
            if( (n = jsdate.getMonth() + 1) == 2 ){ 
                return 28 + f.L(); 
            } else{ 
                if( n & 1 && n < 8 || !(n & 1) && n > 7 ){ 
                    return 31; 
                } else{ 
                    return 30; 
                } 
            } 
        }, 
        
        // Year 
        L: function(){var y = f.Y();return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0}, 
        //o not supported yet 
        Y: function(){return jsdate.getFullYear()}, 
        y: function(){return (jsdate.getFullYear() + "").slice(2)}, 
        
        // Time 
        a: function(){return jsdate.getHours() > 11 ? "pm" : "am"}, 
        A: function(){return f.a().toUpperCase()}, 
        B: function(){ 
            // peter paul koch: 
            var off = (jsdate.getTimezoneOffset() + 60)*60; 
            var theSeconds = (jsdate.getHours() * 3600) + (jsdate.getMinutes() * 60) + jsdate.getSeconds() + off; 
            var beat = Math.floor(theSeconds/86.4); 
            if (beat > 1000) beat -= 1000; 
            if (beat < 0) beat += 1000; 
            if ((String(beat)).length == 1) beat = "00"+beat; 
            if ((String(beat)).length == 2) beat = "0"+beat; 
            return beat; 
        }, 
        g: function(){return jsdate.getHours() % 12 || 12}, 
        G: function(){return jsdate.getHours()}, 
        h: function(){return pad(f.g(), 2)}, 
        H: function(){return pad(jsdate.getHours(), 2)}, 
        i: function(){return pad(jsdate.getMinutes(), 2)}, 
        s: function(){return pad(jsdate.getSeconds(), 2)}, 
        //u not supported yet 
        
        // Timezone 
        //e not supported yet 
        //I not supported yet 
        O: function(){ 
            var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4); 
            if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t; 
            return t; 
        }, 
        P: function(){var O = f.O();return (O.substr(0, 3) + ":" + O.substr(3, 2))}, 
        //T not supported yet 
        //Z not supported yet 
        
        // Full Date/Time 
        c: function(){return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P()}, 
        //r not supported yet 
        U: function(){return Math.round(jsdate.getTime()/1000)} 
    }; 
        
    return format.replace(/[]?([a-zA-Z])/g, function(t, s){ 
        if( t!=s ){ 
            // escaped 
            ret = s; 
        } else if( f[s] ){ 
            // a date function exists 
            ret = f[s](); 
        } else{ 
            // nothing special 
            ret = s; 
        } 
        return ret; 
    }); 
}

axios.defaults.baseURL = location.origin+'/addons/imchat/index/';
// 添加响应拦截器
axios.interceptors.response.use(function (response) {
    // 对响应数据做点什么
    return response.data;
  }, function (error) {
    // 对响应错误做点什么
    console.error('request error');
    return Promise.reject(error);
  });

// 表情
var afeld_emoji= ["em---1","em--1","em-100","em-angry","em-anguished","em-baby","em-blush","em-cold_sweat","em-confounded","em-confused","em-crying_cat_face","em-cry","em-cupid","em-disappointed","em-dizzy_face","em-disappointed_relieved","em-face_with_monocle","em-face_with_raised_eyebrow","em-face_with_rolling_eyes","em-face_vomiting","em-face_with_cowboy_hat","em-expressionless","em-face_with_hand_over_mouth","em-face_with_thermometer","em-face_with_head_bandage","em-fearful","em-frowning","em-full_moon_with_face","em-grimacing","em-grin","em-grinning","em-heart_eyes","em-heart_eyes_cat","em-heart","em-hugging_face","em-hushed","em-innocent","em-joy","em-joy_cat","em-kissing_closed_eyes","em-kissing_heart","em-kissing","em-kissing_smiling_eyes","em-laughing","em-lying_face","em-mask","em-money_mouth_face","em-neutral_face","em-no_mouth","em-open_mouth","em-persevere","em-rage","em-relaxed","em-relieved","em-shushing_face","em-sleeping","em-sleepy","em-slightly_smiling_face","em-slightly_frowning_face","em-smiley","em-smile_cat","em-smirk_cat","em-smirk","em-smile","em-star-struck","em-stuck_out_tongue_closed_eyes","em-stuck_out_tongue_winking_eye","em-sunglasses","em-sweat","em-tired_face","em-thinking_face","em-triumph","em-unamused","em-upside_down_face","em-weary","em-white_frowning_face","em-wink","em-zany_face","em-worried","em-zipper_mouth_face","em-yum"];


new Vue({
  el: "#tichat_vue_app",
  directives: {
    // 过滤掉标签显示指令
    truetext: {
      inserted (el, binding) {
        binding.value && (el.innerText = binding.value.replace(/<\/?.+?>/g,""));
      },
      update (el, binding) {
        if (binding.value !== binding.oldValue) {
          if (binding.value)
            el.innerText = binding.value.replace(/<\/?.+?>/g,"");
          else
            el.innerText = '';
        }
      }
    }
  },
  data: {
    // 聊天tab控制 以及存储每个标签的选中的对象
    chat_control: {
      tab: 'recChat', // 'recChat', 'groupChat', 'userChat'
      recChat: {},
      groupChat: {},
      userChat: {},
    },
    // 最近聊天人员
    recChat: [],
    // 群组聊天人员
    groupChat: [],
    // 所有人员聊天
    userChat:[],
    //本人用户信息
    userInfo: {},
    wss: null, //websocket状态
    sendStatus: {}, // 单一发送标记
    chat_record: {}, // 聊天记录 键值对存储 群组加上group
    current_chat_record: [], // 当前窗口的聊天记录
    scroll_record_control: {}, //滚动条变量存储
    no_record_array: [], //记录没有更多聊天记录标记
    emoji: afeld_emoji, // 表情类名
    rec_reset: true, // 用于重新渲染 最近聊天
    isHasUnreadMsg: false, // 是否有未读的消息
    msg_audio: localStorage.msg_audio != 'false' ? true : false,// 是否开启声音提示

    show_emoji: false, // 显示表情panel
    show_config_panel: false, //显示设置panel
    input_search: {
      content: '', // 搜索内容
      result: [], // 结果
      show_result: false, // 结果是否显示
    }
  },
  watch: {
    // 监听聊天对象改变时赋予不同聊天记录
    'chat_control.recChat': {
      handler(newVal, oldVal) {
        this._refreshCurrent_chat_record ();
      },
      deep: true,
    },
    // 人员搜索
    'input_search.content': {
      handler(newVal, oldVal) {
        newVal = newVal.replace(/^\s*/, '').replace(/\s*$/, '');
        if (!newVal) {
          this.input_search.show_result = false;
          this.input_search.result = [];
          return;
        }
        this.input_search.show_result = true;
        // 先搜索群组 后人员
        // 只需搜索 username nickname name三个字段
        let list = [];
        const reg = new RegExp(newVal, 'i');
        for (let v of this.groupChat) {
          if (v.name.search(reg) != -1) {
            list.push(v);
          }
        }
        for (let v of this.userChat) {
          if (v.username.search(reg) != -1 || v.nickname.search(reg) != -1) {
            list.push(v);
          }
        }
        this.input_search.result = list;
      },
    }
  },
  computed: {
  },
  methods: {
    // 更改是否提示
    change_msg_audio () {
      this.msg_audio = !this.msg_audio;
      localStorage.msg_audio = this.msg_audio;
    },
    // 隐藏其他状态弹出面板
    hide_status_panel() {
      this.show_emoji = false;
      this.show_config_panel = false;
      this.input_search.show_result = false;
    },
    // 直接插入输入框未做到 现在只能调用直接发送
    insert_emoji (e) {
      this.keyup_ctrl_enter('', e.target.outerHTML);
    },
    // 文件上传
    upload (e) {
      if (!this.wss) {
        alert('未连接聊天服务器，请刷新后重试');
        return;
      }
      const file = e.target.files[0];
      if (!file) return;
      // 这里大小限制了8m 
      if (file.size > 8*1024*1024) {
        alert('上次文件不可大于8m');
        return;
      }
      const param = new FormData(); //创建form对象
      param.append('file',file);//通过append向form对象添加数据
      param.append('from_uid',this.userInfo.id); 
      param.append('to_uid',this.chat_control.recChat.id); 
      param.append('type',this.chat_control.recChat.type); 
      // console.log(param.get('file')); //FormData私有类对象，访问不到，可以通过get判断值是否传进去
      const config = {
        headers:{'Content-Type':'multipart/form-data'}
      }; //添加请求头
      axios.post('upload_file',param,config)
        .then(msg=>{
          if (msg.errno != 0) {
            alert(msg.msg ? msg.msg : '上次失败');
          } else {
            // 清空
            e.target.value = '';
            // 插入聊天
            this._appendChatRecord(msg.data);
            const _o = {
              type: msg.data.type,
              id: msg.data.to_uid,
              last_msg: msg.data.content,
              timestamp: msg.data.createtime,
            }
            this._refreshRecChat(_o, 'send');
          }
        })
    },
    // 隐藏图片预览
    hide_preview (e) {
      if (e.target == document.querySelector('.tichat-preview-img')) {
        e.target.style.display = 'none';
      }
    },
    // 是否有更多聊天记录
    is_has_more_record () {
      const {id,type} = this.chat_control.recChat;
      // 先判断是否还有未取出的记录
      const _k = type == 'group' ? 'group_'+id : id;
      // 没有更多了
      if (this.no_record_array.indexOf(_k) != -1) {
        return false;
      }
      return true;
    },
    // 获取更多聊天记录
    getMoreRecord () {
      let req = {
        id: this.chat_control.recChat.id,
        type: this.chat_control.recChat.type,
      }
      // 先判断是否还有未取出的记录
      if (!this.is_has_more_record()) {
        return;
      }
      
      const cr_id = req.type == 'group' ? `group_${req.id}` : req.id;
      if (this.chat_record[cr_id] && this.chat_record[cr_id][0]) {
        req.msg_id = this.chat_record[cr_id][0].msg_id;
      }
      // 服务器查询
      axios.post('getHistoryChatRecord', req).then(m=>{
        if (m.errno == 0) {
          // 没有更多了 插入标记
          if (!m.data.has_more) {
            this.no_record_array.push(cr_id);
          }
          // 插入聊天记录
          this.chat_record[cr_id] = this.chat_record[cr_id] ? 
          [...m.data.data, ...this.chat_record[cr_id]] : 
          m.data.data;
          setTimeout(()=>{
            this._refreshCurrent_chat_record();
          }, 0);
        }
      }).catch();
    },
    // 刷新当前聊天
    _refreshCurrent_chat_record () {
      let k = this.chat_control.recChat.id;
      if (this.chat_control.recChat.type== 'group') {
        k = `group_${k}`;
      }
      this.current_chat_record = this.chat_record[k];
      setTimeout(()=>{
        const nl = document.querySelectorAll('.tichat-record-content img');
        for (let x of nl) {
          x.addEventListener('click', function(){
            // 显示预览
            document.querySelector('.tichat-preview-img img').src = this.src;
            document.querySelector('.tichat-preview-img').style.display="flex";
          });
        }
      }, 0);
    },
    /**
     * 消息发送
     */
    keyup_ctrl_enter (e, emoji_content= '') {
      let input_content = this.$refs.input_content.innerHTML;
      if (emoji_content) {
        input_content = emoji_content;
      }
      if (!input_content) return;
      if (!this.wss) {
        alert('未连接聊天服务器，请刷新后重试');
        return;
      }
      // 保证单一发送
      if (this.sendStatus.send === true) return;
      // 执行发送
      this.sendStatus.send = true;
      const data = {
        content: input_content,
        from_uid: this.userInfo.id,
        to_uid: this.chat_control.recChat.id,
        type: this.chat_control.recChat.type,
      };
      axios.post('sendTxtMsg', data)
        .then(msg=>{
          if (msg.errno != 0) {
            console.error(msg.msg);
            alert(msg.msg);
          } else {
            // 清空输入框 与 显示刷新
            // 向聊天记录中插入
            this._appendChatRecord(msg.data);
            emoji_content || (this.$refs.input_content.innerHTML = '');
            const _o = {
              type: msg.data.type,
              id: msg.data.to_uid,
              last_msg: msg.data.content,
              timestamp: msg.data.createtime,
            }
            this._refreshRecChat(_o, 'send');
          }
          // 发送开启
          this.sendStatus.send = false;
        }).catch(err=>{
          console.error('发送失败');
          this.sendStatus.send = false;
        })
    },
    // 附加聊天记录
    _appendChatRecord (data, is_receive=false) {
      let k = is_receive ? data.from_uid : data.to_uid;
      if (data.type == 'group') {
        k = "group_"+data.to_uid;
      }
      this.chat_record[k] = this.chat_record[k] ? 
        [...this.chat_record[k], data] : 
        [data];
      setTimeout(()=>{
        this._refreshCurrent_chat_record();
        this._scrollMoveBottom();
      }, 0);
    },
    /**
     * 选择聊天对象 
     * 同时切换tab到最近聊天 
     * 赋予人员选中
     * @param  object obj 聊天对象
     * @param  boolean tab 是否tab切换
     */
    select_chat_object (obj, tab=false) {
      // 标记为已读
      this._self_set_read (obj.id, obj.type);
      this.chat_control.recChat = obj;
      if (tab) {
        this.chat_control.tab = 'recChat';
        this._refreshRecChat(obj);
      }
      // 重置滚动条 切换tab需要重新new 否则刷新 
      // 使用定时器原因是压入最后执行堆栈简单处理
      setTimeout(()=>{
        if (tab || !this.scroll_record_control.destroy) {
          this.scroll_record_control.destroy && this.scroll_record_control.destroy();
          this.scroll_record_control =  new PerfectScrollbar('#tichat_chat_record');
        } else {
          this.scroll_record_control.update();
        }
        // 移动到最后\
        this._scrollMoveBottom();
      },0);
    },
    /**
     * 标记已读 如果本地都已读无需发送到服务器
     * 同时维护是否有未读消息
     * @param  int id
     * @param  string type 群组还是个人
     */
    _self_set_read (id, type) {
      let is_has_unread = false;
      // for in 遍历需要注意 继承的属性以及__ob__
      for (let i in this.recChat) {
        if (this.recChat[i].id == id && this.recChat[i].type == type) {
          if (this.recChat[i].unread_count > 0) {
            this.recChat[i].unread_count = 0;
            this.rec_reset = false;
            this.rec_reset = true;
            // 服务器标记已读
            axios.post('setRead', {id,type})
              .then(m=>{
              }).catch(err=>{console.error(err.msg)});
          }
        }
        // 有未读消息设置为有未读
        if (this.recChat[i].unread_count > 0) {
          is_has_unread = true;
        }
      }
      this.isHasUnreadMsg = is_has_unread;
    },
    // 聊天滚动条到底部
    _scrollMoveBottom ()
    {
      setTimeout(()=>{
        try{
          document.querySelector('#tichat_chat_record').scrollTop = document.querySelector('#tichat_chat_record').scrollHeight;
        }catch(err){}
      },0);
    },
    /**
     * 是否聊天记录中显示时间与日期
     * @param  int  i 数组所处位置
     * @return string 时间或者时间日期 或者void
     */
    is_show_date (i) {
      const c = this.current_chat_record;
      if (i == 0) {
        return this.show_format_datetime(c[0].createtime);
      } else if(c[i].createtime - c[i-1].createtime > 180) {
        return this.show_format_datetime(c[i].createtime);
      }
    },
    /**
     * 需要判断是否已在最近聊天中
     * 存在的话将其替换到最前面 不存在的话插入到最前面
     * 此方法在标签跳转和收到消息时调用 直接点击不调用
     * 群组消息特殊处理 如果此群组不在最近聊天之内 需要去群组里查找群组信息显示
     * @param  object obj 聊天对象
     * @param  string tab 刷新类别
     */
    _refreshRecChat (obj, type='tab') {
      // 如果有的话提取到第一位
      if (type == 'tab') {
        for (let i = 0; i < this.recChat.length; i++) {
          if (this.recChat[i].id == obj.id && this.recChat[i].type == obj.type) {
            obj = this.recChat.splice(i, 1)[0];
            break;
          }
        }
        this.recChat.unshift(obj);
      }
      if (type == 'send') {
        let _o = obj;
        for (let i = 0; i < this.recChat.length; i++) {
          if (this.recChat[i].id == obj.id && this.recChat[i].type == obj.type) {
            _o = this.recChat.splice(i, 1)[0];
            // 这个一定存在 更新时间 重置未读聊天 更新最后一条
            _o.timestamp= obj.timestamp;
            _o.unread_count= 0;
            _o.last_msg= obj.last_msg;
            break;
          }
        }
        this.recChat.unshift(_o);
      }
      if (type == 'receive') {
        let search_group_tag = false; //是否在最近聊天中找到
        let _o = obj;
        for (let i = 0; i < this.recChat.length; i++) {
          // 先在最近聊天中寻找 有的话就直接处理 提取到最前面
          if (this.recChat[i].id == obj.id && this.recChat[i].type == obj.type) {
            search_group_tag = true;
            _o = this.recChat.splice(i, 1)[0];
            _o.timestamp= obj.timestamp;
            _o.last_msg= obj.last_msg;
            _o.unread_count= _o.unread_count ? ++_o.unread_count : 1;
            break;
          }
        }
        // 收到消息时 是群组消息 并且未找到 
        if (!search_group_tag && obj.type == 'group') {
          for (let v of this.groupChat) {
            if (v.id == obj.id) {
              // 构建新的展示信息
              _o = {
                ...v,
                unread_count: 1,
                last_msg: obj.last_msg,
                timestamp: obj.timestamp,
              };
            }
          }
        }
        this.recChat.unshift(_o);
      }
      
    },
    /**
     * 查看联系人对象 赋值群组还是人员
     * @param  [object] obj 聊天对象
     * @param  {String} type 群组还是人员
     */
    select_contact_object (obj, type="") {
      if (obj.type == 'group') {
        this.chat_control.groupChat = obj;
      }else {
        this.chat_control.userChat = obj;
      }
    },
    // 播放提示声
    play_newmsg_audio () {
      const a = document.createElement("AUDIO");
      a.src="/assets/addons/imchat/new_message.mp3";
      a.play();
    },
    // 消息输入框获取焦点时
    input_focus () {
      this._self_set_read (this.chat_control.recChat.id, this.chat_control.recChat.type);
    },
    /**
     * 收到消息时处理
     * 需要加入聊天记录
     * 维护左边最近聊天
     * 刷新右边框聊天记录
     * @param  Object data 数据对象
     */
    _receive_msg (data) {
      /**
       * 维护左边最近聊天
       * 先拼接最近聊天格式 之所以不去用户里找
       * 一是由于浪费资源
       * 二是由于有新用户消息时也要显示
       * 所以只要保证主要数据格式一致即可 id 和tpye必须
       */
      // 先生成所需数据格式
      let _o = {
          type: data.type,
          id: data.type=='group' ? data.to_uid : data.from_uid,
          nickname: data.userInfo.nickname,
          username: data.userInfo.username,
          avatar: data.userInfo.avatar,
          last_msg: data.content,
          timestamp: data.createtime,
          unread_count: 1,
        }
      this._refreshRecChat(_o, 'receive');
      // 加入聊天记录 然后刷新右边框聊天记录
      this._appendChatRecord(data, true);
      this.isHasUnreadMsg = true;
      this.msg_audio && this.play_newmsg_audio();
    },
    /**
     * 显示时间戳 当天不显示日期
     * @param  {int} timestamp 时间戳
     * @return {String}        时间
     */
    show_format_datetime (timestamp) {
      return date("m-d",timestamp) == date('m-d') ? date('H:i', timestamp) : date('m/d H:i', timestamp)
    },
    /**
     * 监听websocket消息
     * @param  {String} message 收到的消息
     */
    _on_message (message) {
      try{
        const re = JSON.parse(message.data);
        switch (re.type) {
          case 'register':
            this._ws_register(re.data.client_id, this.userInfo.cert);
            break;
          case 'html':
            this._receive_msg(re.data);
            break;
        }
      }catch(err){
        //解析json 不是则忽略
        console.error('not is json data');
        console.error(err);
      }
    },
    /**
     * 去服务器注册绑定聊天
     * @param  {String} client_id 聊天服务器分配的id
     * @param  {String} cert 后台服务器分配的凭证
     */
    _ws_register (client_id, cert) {
      axios.post('bindWSUser', {
        client_id,cert
      }).then(m=>{
        if (m.errno != 0) {
          this.wss = null;
          console.error(m.msg);
        }
      }).catch(err=>{console.error('bind error')})
    }
  },
  created () {
    // 获取用户信息以及连接凭证
    axios.get('cert')
      .then(msg=> {
        this.userInfo = msg.user;
        // 连接websocket
        try {
          const socket_addr = msg.socket_addr ? msg.socket_addr : "ws://"+location.host+':7887';
          const ws = new WebSocket(socket_addr);
          this.wss = true;
          ws.onmessage = this._on_message;
          ws.onclose = event=> {
            this.wss = null; //状态置为不连接
          };
          ws.onerror = event=>{
            this.wss = null;
          };
        } catch( err ) {
          console.error('连接聊天服务器失败');
        }
      }).catch(err=> {
        alert('获取用户信息失败');
      });
    // 获取聊天信息
    axios.get('chatList')
      .then(msg=> {
        this.recChat = msg.rec_chat;
        this.groupChat = msg.chat_group;
        // 做了一个简单排序
        this.userChat = msg.user_list.sort(function(a,b){return a.nickname.toString().localeCompare(b.nickname)});
        this.chat_record = msg.chat_record;
        // 是否有新消息
        if (msg.rec_chat && msg.rec_chat.length > 0) {
          this.isHasUnreadMsg = true;
        }
      }).catch();
  },
  mounted () {
    new PerfectScrollbar('#tichat_rec_chat');
    new PerfectScrollbar('#tichat_group_chat');
    new PerfectScrollbar('#tichat_user_chat');
  }
});

(function () {
  var el = document.querySelector('#tichat_el');
  var avail = 'translate('+window.screen.availWidth+'px, '+window.screen.availHeight+'px)  scale(.00001)';
  el.style.transform = avail;
  el.style.opacity = 1;

  document.querySelector('#tichat_small_show').onclick= function() {
    el.style.transition = 'all .6s';
    el.style.transform = 'inherit';
    el.style.opacity = 1;
  }
  document.querySelector('#tichat_chat_area_close').onclick= function() {
    el.style.transform = avail;
    el.style.opacity = 0;
  }
  document.querySelector('#tichat_vue_app').onkeyup = function (e) {
    if (e.keyCode === 27) {
      el.style.transform = avail;
      el.style.opacity = 0;
    }
  }
})();

