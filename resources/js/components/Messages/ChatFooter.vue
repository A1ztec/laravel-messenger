<template>
    <div class="chat-footer pb-3 pb-lg-7 position-absolute bottom-0 start-0">
        <!-- Chat: Files Preview -->
        <div class="dz-preview bg-dark" id="dz-preview-row" data-horizontal-scroll="">
            <!-- Dynamically handled file previews -->
        </div>

        <!-- Chat: Message Form -->
        <form class="chat-form rounded-pill bg-dark" @submit.prevent="sendMessage()" method="post" enctype="multipart/form-data">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="conversation_id" :value="conversation ? conversation.id : 0">

            <div class="row align-items-center gx-0">
                <!-- Attachment Button -->
                <div class="col-auto">
                    <a href="#" class="btn btn-icon btn-link text-body rounded-circle" @click.prevent="handleAttachmentClick()" aria-label="Attach file">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                        </svg>
                    </a>
                </div>

                <!-- Message Input Field -->
                <div class="col">
                    <div class="input-group">
                        <textarea class="form-control px-0" v-model="message" @focus="$root.markAsRead()" @keypress="startTyping()"   placeholder="Type your message..." rows="1" @input="autoResize" data-emoji-input></textarea>

                        <!-- Emoji Picker Button -->
                        
                    </div>
                </div>

                <!-- Send Button -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-icon btn-primary rounded-circle ms-5" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>


<script>
export default {
    data() {
        return {
            message: '',
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // CSRF token from meta tag
            conversationId: '', // Set dynamically or via props
            showEmojiPicker: false, // Emoji picker visibility
            start_typing : false,
            timeout : null ,
            attachment : '' ,
        };
    },
    methods: {

        startTyping() {
            if(!this.start_typing) {
                this.start_typing = true;
                this.$root.chatChannel.whisper('typing', {
                    id: this.$root.userId,
                    conversation_id: this.$root.conversation.id
                });
            }
            if(this.timeout){
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(() => {
                this.start_typing = false;
                this.$root.chatChannel.whisper('stop-typing', {
                    id: this.$root.userId,
                    conversation_id: this.$root.conversation.id
                });
            }, 500);
        },

        sendMessage() {
            let formData = new FormData();
            formData.append('conversation_id', this.$root.conversation.id);
            formData.append('message', this.message);
            formData.append('_token', this.$root.csrfToken);
            if (this.attachment) {
                formData.append('attachment', this.attachment);
            }

            if (this.message.trim() || this.attachment) {
                fetch('/api/messages', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then(response => response.json())
                    .then(json => {
                        this.$root.messages.push(json);
                        this.message = '';
                        this.attachment = null;
                        let container = document.querySelector('#chat-body');
                        container.scrollTop = container.scrollHeight;
                    });
            }
        },
        handleAttachmentClick() {
            let fileElm = document.createElement('input');
            fileElm.type = 'file';
            fileElm.addEventListener('change', (e) => {
                if (fileElm.files.length === 0) {
                    return;
                }
                this.attachment = fileElm.files[0];
                this.sendMessage(this.attachment);
            });
            fileElm.click();
        },


        toggleEmojiPicker() {
            this.showEmojiPicker = !this.showEmojiPicker;
            // Implement emoji picker toggle logic
            console.log('Emoji picker toggled:', this.showEmojiPicker);
        },
        autoResize(event) {
            const textarea = event.target;
            textarea.style.height = 'auto'; // Reset height
            textarea.style.height = `${textarea.scrollHeight}px`; // Adjust height
        },
    },

    props : {
        conversation: {
            type: [Object, null],
            required: true
        }
    }
};
</script>

<style scoped>

.chat-form {
    border: 1px solid #444; /* Subtle border for the form */
    background-color: #343a40; /* Dark background matching the footer */
    padding: 0.75rem; /* Adjust padding for better spacing */
    border-radius: 20px; /* Rounded pill shape for the form */
}

.dz-preview {
    margin-bottom: 1rem; /* Space between file preview and input */
}

.input-group {
    display: flex; /* Flex layout for input group */
}

.input-group-text {
    cursor: pointer; /* Pointer cursor for emoji picker button */
}

.btn-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

textarea {
    resize: none; /* Prevent resizing */
    background-color: transparent; /* Transparent background for dark theme */
    border: none; /* No border */
    color: white; /* White text */
    padding: 0.75rem 1rem; /* Padding for the input */
}

.btn-primary {
    background-color: #1a73e8;
    border-color: #1a73e8;
}

textarea:focus {
    outline: none; /* Remove default focus outline */
}

textarea::placeholder {
    color: #aaa; /* Placeholder text color */
}

</style>
