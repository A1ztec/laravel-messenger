import { createApp } from 'vue';
import Messenger from "./components/Messages/Messanger.vue";
import ChatList from "./components/Messages/ChatList.vue";
import FriendList from "./components/Messages/FrinedList.vue";
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';
window.Pusher = Pusher;



const chatApp = createApp({
    data() {
        return {
            conversations: [],
            conversation: null,
            messages: [],
            userId : userId ,
            user_avatar : user_avatar ,
            user_image : user_image ,
            csrfToken : csrf,
            laravelEcho : null ,
            users : [] ,
            chatChannel : null ,
            alertAudio : new Audio('assets/mixkit-correct-answer-tone-2870.wav')
        }
    },
    mounted() {
        this.alertAudio.addEventListener('ended' , function(){
            this.alertAudio.currentTime = 0 ;
        })
        this.laravelEcho = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        this.laravelEcho
            .join(`Messenger.${this.userId}`)
            .listen('.new-message', (data) => {
                let exists = false;
                for (let i in this.conversations) {
                    let conversation = this.conversations[i];
                    if (conversation.id == data.message.conversation_id) {
                        if (!conversation.hasOwnProperty('new_messages')) {
                            conversation.new_messages = 0;
                        }
                        conversation.new_messages++;
                        conversation.last_message = data.message;
                        exists = true;
                        this.conversations.splice(i, 1);
                        this.conversations.unshift(conversation);

                        if (this.conversation && this.conversation.id == conversation.id) {
                            this.messages.push(data.message);
                            let container = document.querySelector('#chat-body');
                            container.scrollTop = container.scrollHeight;
                        }
                        break;
                    }
                }
                if (!exists) {
                    fetch(`/api/conversations/${data.message.conversation_id}`)
                        .then(response => response.json())
                        .then(json => {
                            this.conversations.unshift(json)
                        })
                }

                this.alertAudio.play();

            })



        let chatChannel =  this.laravelEcho
            .join('chat')
            .joining((user) => {
                for(let i in this.conversations){
                    let conversation = this.conversations[i];
                    if(conversation.participants[0].id == user.id){
                        this.conversations[i].participants[0].isOnline = true;
                        return ;
                    }
                }
            })
            .leaving((user) => {
                for(let i in this.conversations){
                    let conversation = this.conversations[i];
                    if(conversation.participants[0].id == user.id){
                        this.conversations[i].participants[0].isOnline = false;
                        return ;
                    }
                }
            })
         this.chatChannel = chatChannel

         .listenForWhisper('typing', (e) => {
             let user = this.findUser(e.id , e.conversation_id);
             if(user){
                 user.isTyping = true;
             }
         })
         .listenForWhisper('stop-typing', (e) => {
             let user = this.findUser(e.id , e.conversation_id);
             if(user){
                 user.isTyping = false;
             }
         });





    },
    methods: {
        markAsRead(conversation = null) {
            if (conversation == null) {
                conversation = this.conversation;
            }
            fetch(`/api/conversations/${conversation.id}/read`, {
                method: 'PUT',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: JSON.stringify({
                    _token: this.$root.csrfToken
                })
            })
                .then(response => response.json())
                .then(json => {
                    conversation.new_messages = 0;
                })
        },
        moment(time) {
            return moment(time);
        },
        isOnline(user){
            for(let i in this.users){
                if(this.users[i].id == user.id){
                    return this.users[i].isOnline;
                }
            }
            return false;
        },
        findUser(id , converation_id){
            for(let i in this.conversations){
                let conversation = this.conversations[i];
                if(conversation.participants[0].id == id && conversation.id === converation_id){
                 return this.conversations[i].participants[0];
                }
            }
        },
        deleteMessage(message, target) {
            fetch(`/api/messages/${message.id}`, {
                method: 'DELETE',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    target: target,
                    _token: this.$root.csrfToken
                })
            })
                .then(response => response.json())
                .then(json => {
                    console.log(json);
                    if (json.success) {

                        message.body = 'Message deleted..';
                        }


                })
                .catch(error => {
                    console.error('Error deleting message:', error);
                });
        }


    },
})
    .component('messenger', Messenger)
    .component('ChatList' , ChatList)
    .component('FriendList' , FriendList)
    .mount('#chat-app');
