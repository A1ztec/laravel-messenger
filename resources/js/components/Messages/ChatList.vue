<template>

    <div class="container py-8">
        <!-- Title -->
        <div class="mb-8">
            <h2 class="fw-bold m-0">Chats</h2>
        </div>

        <!-- Search -->

        <!-- Chats -->
        <div class="card-list" id="chat-list">

            <a v-for="conversation in $root.conversations" v-bind:key="conversation.id" v-bind:href="'#' + conversation.id"  @click.prevent="setConversation(conversation)" class="card border-0 text-reset">
                <div class="card-body">
                    <div class="row gx-5">
                        <div class="col-auto">
                            <div class="avatar" :class="{'avatar-online' : conversation.participants[0].isOnline}">
                                <img v-if="conversation.participants[0].image_url" :src="conversation.participants[0].image_url"  alt="#" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                <img v-else :src="conversation.participants[0].avatar_url" alt="#" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                            </div>
                        </div>

                        <div class="col">
                            <div class="d-flex align-items-center mb-3">
                                <h5 class="me-auto mb-0">{{conversation.participants[0].name}}</h5>
                                <span class="text-muted extra-small ms-2">{{ moment(conversation.last_message.created_at).fromNow() }}</span>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="line-clamp me-auto">
                                    {{ conversation.last_message.type == 'attachment'? conversation.last_message.body.file_name : conversation.last_message.body }}
                                </div>

                                <div v-if="conversation.new_messages" class="badge badge-circle bg-primary ms-5">
                                    <span>{{ conversation.new_messages }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- .card-body -->
            </a>
            <!-- Card -->


        </div>

    </div>

</template>

<script>

import {nextTick} from "vue";

export default {
data() {
return {


};
},
mounted () {
 // fetch here return something called promise
    fetch('/api/conversations')
       .then(response => response.json())
        .then(json => {
            this.$root.conversations = [];
            for(let i in json.data) {
                json.data[i].participants[0].isOnline = false;

            }
            this.$root.conversations = (json.data);
        });

},
methods : {

    setConversation(conversation) {
        this.$root.conversation = conversation;
        this.$root.markAsRead(conversation);
        nextTick(() => {
            let container = document.querySelector('#chat-body');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    },
    moment(date) {
        return moment(date);
    },

}





}

</script>
