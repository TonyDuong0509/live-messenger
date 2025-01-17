/**
 * ------------------------------------------
 * Global Variables
 * ------------------------------------------
 */

let temporaryMsgId = 0;

const messageForm = $(".messenger-form");
const messageInput = $(".message-input");
const messageBoxContainer = $(".wsus__chat_area_body");
const csrf_token = $("meta[name=csrf_token]").attr("content");

const getMessengerId = () => {
    return $("meta[name=id]").attr("content");
};

const setMessengerId = (id) => {
    $("meta[name=id]").attr("content", id);
};

/**
 * ------------------------------------------
 * Reuseable Functions
 * ------------------------------------------
 */

const enableChatBoxLoader = () => {
    $(".wsus__message_paceholder").removeClass("d-none");
};

const disableChatBoxLoader = () => {
    $(".wsus__message_paceholder").addClass("d-none");
};

const imageFilePreview = (input, selector) => {
    if (input.files && input.files[0]) {
        let render = new FileReader();

        render.onload = function (e) {
            $(selector).attr("src", e.target.result);
        };

        render.readAsDataURL(input.files[0]);
    }
};

let searchPage = 1;
let noMoreDataSearch = false;
let searchTempVal = "";
let setSearchLoading = false;
const searchUsers = (query) => {
    if (query != searchTempVal) {
        searchPage = 1;
        noMoreDataSearch = false;
    }
    searchTempVal = query;

    if (!setSearchLoading && !noMoreDataSearch) {
        $.ajax({
            type: "GET",
            url: "/messenger/search",
            data: { query: query, page: searchPage },
            beforeSend: function () {
                setSearchLoading = true;
                let loader = `
                    <div class="text-center search-loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                $(".user_search_list_result").append(loader);
            },
            success: function (response) {
                setSearchLoading = false;
                $(".user_search_list_result").find(".search-loader").remove();
                if (searchPage < 2) {
                    $(".user_search_list_result").html(response.records);
                } else {
                    $(".user_search_list_result").append(response.records);
                }

                noMoreDataSearch = searchPage >= response?.last_page;
                if (!noMoreDataSearch) searchPage += 1;
            },
            error: function (xhr, status, error) {
                setSearchLoading = false;
                $(".user_search_list_result").find(".search-loader").remove();
            },
        });
    }
};

const actionOnScroll = (selector, callback, topScroll = false) => {
    $(selector).on("scroll", function () {
        let element = $(this).get(0);
        const condition = topScroll
            ? element.scrollTop == 0
            : element.scrollTop + element.clientHeight >= element.scrollHeight;

        if (condition) {
            callback();
        }
    });
};

const debounce = (callback, delay) => {
    let timerId;
    return function (...args) {
        clearTimeout(timerId);
        timerId = setTimeout(() => {
            callback.apply(this, args);
        }, delay);
    };
};

/**
 * ------------------------------------------
 * Fetch id data of user and update the view
 * ------------------------------------------
 */

const IDinfo = (id) => {
    $.ajax({
        method: "GET",
        url: "message/id-info",
        data: { id: id },
        beforeSend: function () {
            NProgress.start();
            enableChatBoxLoader();
        },
        success: function (response) {
            $(".messenger-header")
                .find("img")
                .attr("src", response.fetch.avatar);
            $(".messenger-header").find("h4").text(response.fetch.name);
            $(".messenger-info-view .user_photo")
                .find("img")
                .attr("src", response.fetch.avatar);
            $(".messenger-info-view")
                .find(".user_name")
                .text(response.fetch.name);
            $(".messenger-info-view")
                .find(".user_unique_name")
                .text(response.fetch.user_name);
            NProgress.done();
            disableChatBoxLoader();
        },
        error: function (xhr, status, error) {
            NProgress.done();
            disableChatBoxLoader();
        },
    });
};

/**
 * ------------------------------------------
 * Send Message
 * ------------------------------------------
 */

const sendMessage = () => {
    temporaryMsgId += 1;
    let tempID = `temp_${temporaryMsgId}`;
    let hasAttachment = !!$(".attachment-input").val();
    const inputValue = messageInput.val();

    if (inputValue.length > 0 || hasAttachment) {
        const formData = new FormData($(".messenger-form")[0]);
        formData.append("id", getMessengerId());
        formData.append("temporaryMsgId", tempID);
        formData.append("_token", csrf_token);

        $.ajax({
            type: "POST",
            url: "/messenger/send-message",
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function () {
                // Add temp message on dom
                if (hasAttachment) {
                    messageBoxContainer.append(
                        sendTempMessageCard(inputValue, tempID, true)
                    );
                } else {
                    messageBoxContainer.append(
                        sendTempMessageCard(inputValue, tempID)
                    );
                }
                messageFormReset();
            },
            success: function (response) {
                const tempMsgCardElement = messageBoxContainer.find(
                    `.message-card[data-id=${response.tempID}]`
                );
                tempMsgCardElement.before(response.message);
                tempMsgCardElement.remove();
            },
            error: function (xhr, status, error) {},
        });
    }
};

const sendTempMessageCard = (message, tempId, attachment = false) => {
    if (attachment) {
        return `
        <div class="wsus__single_chat_area message-card" data-id=${tempId}>
            <div class="wsus__single_chat chat_right">
                <div class="pre_loader">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                ${
                    message.length > 0
                        ? `<p class="messages">${message}</p>`
                        : ""
                }
                 <span class="far fa-clock"> đang gửi...</span>
                <a class="action" href="#"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    `;
    } else {
        return `
        <div class="wsus__single_chat_area message-card" data-id=${tempId}>
            <div class="wsus__single_chat chat_right">
                <p class="messages">${message}</p>
                <span class="far fa-clock"> đang gửi...</span>
                <a class="action" href="#"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    `;
    }
};

const messageFormReset = () => {
    $(".attachment-block").addClass("d-none");
    $(".emojionearea-editor").text("");
    messageForm.trigger("reset");
};

/**
 * ------------------------------------------
 * On Dum Load
 * ------------------------------------------
 */

$(document).ready(function () {
    $("#select_file").on("change", function () {
        imageFilePreview(this, ".profile-image-preview");
    });

    // Search action on 'keyup'
    const debouncedSearch = debounce(function () {
        let value = $(".user_search").val();
        searchUsers(value);
    }, 500);

    $(".user_search").on("keyup", function () {
        let query = $(this).val();
        if (query.length > 0) {
            debouncedSearch();
        }
    });

    // Search pagination
    actionOnScroll(".user_search_list_result", function () {
        let value = $(".user_search").val();
        searchUsers(value);
    });

    // Click action for messenger list item
    $("body").on("click", ".messenger_list_item", function () {
        const dataId = $(this).data("id");
        setMessengerId(dataId);
        IDinfo(dataId);
    });

    // Send message
    messageForm.on("submit", function (event) {
        event.preventDefault();
        sendMessage();
    });

    // Send Attachment
    $(".attachment-input").on("change", function () {
        imageFilePreview(this, ".attachment-preview");
        $(".attachment-block").removeClass("d-none");
    });

    $(".cancel-attachment").on("click", function () {
        messageFormReset();
    });
});
