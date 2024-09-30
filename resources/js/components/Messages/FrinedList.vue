<template>
    <div class="container py-8">
        <!-- Title -->
        <div class="mb-8">
            <h2 class="fw-bold m-0">Friends</h2>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <form @submit.prevent="searchFriends">
                <div class="input-group">
                    <div class="input-group-text">
                        <div class="icon icon-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>
                    <input
                        type="text"
                        class="form-control form-control-lg ps-0"
                        placeholder="Search messages or users"
                        aria-label="Search for messages or users..."
                        v-model="searchQuery"
                    />
                </div>
            </form>

            <!-- Invite button -->

        </div>

        <!-- List -->
        <div class="card-list">
            <div v-if="groupedFriends.length === 0">
                <p>No friends found.</p>
            </div>
            <div v-for="(friendGroup, index) in groupedFriends" :key="index" class="my-5" >
                <small class="text-uppercase text-muted">{{ friendGroup.letter }}</small>
                <div v-for="friend in friendGroup.friends" :key="friend.id" class="card border-0" @click.prevent="openConversation(friend)">
                    <div class="card-body">
                        <div class="row align-items-center gx-5">
                            <div class="col-auto">
                                <a href="#" class="avatar">
                                    <img class="avatar-img" v-if="friend.image_url" :src="friend.image_url" alt="">
                                    <img class="avatar-img" v-else :src="friend.avatar_url" alt="">
                                </a>
                            </div>
                            <div class="col">
                                <h5><a href="#">{{ friend.name }}</a></h5>
                                <p>{{ friend.last_seen_at }}</p>
                            </div>
                            <div class="col-auto">
                                <!-- Dropdown -->
                                <div class="dropdown">
                                    <a class="icon text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical">
                                            <circle cx="12" cy="12" r="1"></circle>
                                            <circle cx="12" cy="5" r="1"></circle>
                                            <circle cx="12" cy="19" r="1"></circle>
                                        </svg>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">New message</a></li>
                                        <li><a class="dropdown-item" href="#">Edit contact</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#">Block user</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import debounce from 'lodash/debounce';
import {nextTick} from "vue";

export default {
    data() {
        return {
            friends: [], // Stores the list of friends
            searchQuery: '', // The search input value
        };
    },
    computed: {
        groupedFriends() {
            const groups = {};
            if (Array.isArray(this.friends)) {
                this.friends.forEach(friend => {
                    const letter = friend.name.charAt(0).toUpperCase(); // Group friends by the first letter of their name
                    if (!groups[letter]) {
                        groups[letter] = { letter, friends: [] };
                    }
                    groups[letter].friends.push(friend);
                });
            }
            return Object.values(groups).sort((a, b) => a.letter.localeCompare(b.letter)); // Return sorted groups
        },
    },
    methods: {
        fetchFriends() {
            // Fetch all friends
            fetch(`api/friends/${this.$root.userId}`)
                .then(response => response.json())
                .then(json => {
                    this.friends = json.data; // Update friends list
                })
                .catch(error => {
                    console.error('Error fetching friends:', error);
                });
        },
        searchFriends() {
            if (this.searchQuery.length < 3) {
                return;
            }

            fetch(`api/friends-search?search=${this.searchQuery}`)
                .then(response => response.json())
                .then(json => {
                    this.friends = json.data;
                })
                .catch(error => {
                    console.error('Error searching friends:', error);
                });
        },
        openConversation(friend) {
            console.log('Opening conversation with:', friend.name);
            this.$root.conversation = null; // Reset the current conversation

            // Fetch the conversation for the selected friend
            fetch(`/api/friend/conversation/${friend.id}`)
                .then(response => response.json())
                .then(json => {
                    const fetchedConversation = json.body;

                    // Check if the conversation already exists in the conversations array
                    const conversationExists = this.$root.conversations.some(conversation => conversation.id === fetchedConversation.id);

                    if (!conversationExists) {
                        // Add the new conversation to the conversations array
                        this.$root.conversations.unshift(fetchedConversation);
                    }

                    // Set the fetched conversation as the current conversation
                    this.$root.conversation = fetchedConversation;
                    console.log('Conversation:', this.$root.conversation);

                    // If the conversation has participants, mark as read and scroll
                    if (this.$root.conversation && this.$root.conversation.participants.length > 0) {
                        // Mark the conversation as read
                        this.$root.markAsRead(fetchedConversation);

                        // Ensure the DOM updates before scrolling
                        this.$nextTick(() => {
                            let container = document.querySelector('#chat-body');
                            if (container) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    } else {
                        console.log('No participants or messages in this conversation.');
                    }
                })
                .catch(error => {
                    console.error('Error opening conversation:', error);
                });
        }



    },
    watch: {
        searchQuery: debounce(function (newSearchQuery) {
            if (newSearchQuery.length >= 3) {
                this.searchFriends();
            } else if (newSearchQuery.length === 0) {
                this.fetchFriends();
            }
        }, 300),
    },
    mounted() {
        this.fetchFriends(); // Fetch all friends on component mount
    },
};
</script>
