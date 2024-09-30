<template>
    <div class="chat-header border-bottom py-4 py-lg-7">
        <div class="row align-items-center">

            <!-- Mobile: Close Button -->
            <div class="col-2 d-xl-none">
                <a class="icon icon-lg text-muted" href="#" @click.prevent="closeChat" aria-label="Close chat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
            </div>
            <!-- Mobile: Close Button -->

            <!-- Content -->
            <div class="col-8 col-xl-12">
                <div class="row align-items-center text-center text-xl-start">
                    <!-- Title -->
                    <div class="col-12 col-xl-6">
                        <div class="row align-items-center gx-5">
                            <div class="col-auto">
                                <!-- Avatar (Large for main participant) -->
                                <div class="avatar" :class="{'avatar-online' : conversation.participants[0].isOnline}">
                                    <img v-if="conversation.participants[0].image_url"
                                         :src="conversation.participants[0].image_url"
                                         alt="#"
                                         class="rounded-circle"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                    <img v-else
                                         :src="conversation.participants[0].avatar_url"
                                         alt="#"
                                         class="rounded-circle"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>

                            <div class="col overflow-hidden">
                                <h5 class="text-truncate">{{ conversation.participants[0].name }}</h5>
                                <p class="text-truncate" v-if="conversation.participants[0].isTyping">
                                    is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Title -->

                    <!-- Toolbar -->
                    <div class="col-xl-6 d-none d-xl-block">
                        <div class="row align-items-center justify-content-end gx-6">
                            <div class="col-auto">
                                <a href="#" class="icon icon-lg text-muted" @click="showMoreOptions" aria-label="More options">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="19" cy="12" r="1"></circle>
                                        <circle cx="5" cy="12" r="1"></circle>
                                    </svg>
                                </a>
                            </div>

                            <div class="col-auto">
                                <div class="avatar-group">
                                    <!-- Participant Avatar -->
                                    <a href="#" class="avatar avatar-sm" @click="showUserProfile" aria-label="User Profile" style="width: 32px; height: 32px; overflow: hidden;">
                                        <img v-if="conversation.participants[0].image_url"
                                             :src="conversation.participants[0].image_url"
                                             alt="#"
                                             class="rounded-circle"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                        <img v-else
                                             :src="conversation.participants[0].avatar_url"
                                             alt="#"
                                             class="rounded-circle"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </a>

                                    <!-- User's Avatar -->
                                    <a href="#" class="avatar avatar-sm" @click="showProfile" aria-label="Profile" style="width: 32px; height: 32px; overflow: hidden;">
                                        <img v-if="this.$root.user_image"
                                             :src="this.$root.user_image"
                                             alt="#"
                                             class="rounded-circle"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                        <img v-else
                                             :src="this.$root.user_avatar"
                                             alt="#"
                                             class="rounded-circle"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Toolbar -->
                </div>
            </div>
            <!-- Content -->

            <!-- Mobile: More Options -->
            <div class="col-2 d-xl-none text-end">
                <a href="#" class="icon icon-lg text-muted" @click="showMoreOptions" aria-label="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </a>
            </div>
            <!-- Mobile: More Options -->

        </div>
    </div>
</template>


<script>
export default {
    data() {
        return {
            chatAvatar: 'path/to/chat-avatar.jpg', // Replace with the actual path or URL
            chatName: '', // Replace with dynamic chat name
            isTyping: false, // Replace with dynamic typing status
            userProfileImage: '', // Replace with the actual path or URL
            profileImage: '' // Replace with the actual path or URL
        };
    },
    props: {
        conversation: {
            type: [Object, null],
            default: null
        }
    },
    methods: {
        closeChat() {
            // Logic to close the chat (e.g., emit an event or modify a property)
            this.$emit('close-chat');
        },
        showMoreOptions() {
            // Logic to show more options (e.g., open a modal or dropdown)
            this.$emit('show-more-options');
        },
        showUserProfile() {
            // Logic to show user profile (e.g., open a modal with user profile)
            this.$emit('show-user-profile');
        },
        showProfile() {
            // Logic to show profile (e.g., open a modal with profile information)
            this.$emit('show-profile');
        }
    }
};
</script>

<style scoped>
.typing-dots {
    display: inline-block;
    font-size: 0.9em;
}
.typing-dots span {
    display: inline-block;
    animation: typingDots 1.5s infinite;
}
.typing-dots span:nth-child(2) {
    animation-delay: 0.3s;
}
.typing-dots span:nth-child(3) {
    animation-delay: 0.6s;
}

@keyframes typingDots {
    0%, 20% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    80%, 100% {
        opacity: 0;
    }
}
</style>
