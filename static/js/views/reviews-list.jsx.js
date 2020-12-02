function Checkbox (props) {
    return (
        <label class="checkbox">
            <input checked={props.checked} onChange={props.onChange} type="checkbox" value={props.value || props.label}/>
            <text>{props.label}</text>
        </label>
    );
}
function Dropdown (props) {
    if (props.children.length === 0) {
        return null;
    }
    return (
        <button label={props.label} theme={props.theme} className="dropdown">
            <dropdown className={props.right ? 'right' : ''}>
                {props.children}
            </dropdown>
        </button>
    )
}
function deleteReview(review_id) {
    confirmation('Are you want to delete this review?')
    .then((res) => {
        if (! res) {
            return;
        }
        api('delete_review',{
            data: {
                chapter_id,
                review_id
            }
        })
        .then(response => {
            if (response.code > 5) {
                new toast('An error occured.');
            }
            updateReviews();
        });
    });
}
function Review (props) {
    const fill = [];
    if (props.can_reply) {
        fill.push(<li tabindex="0" label="Reply"/>);
    }
    if (props.can_delete) {
        fill.push(<li tabindex="0" onClick={deleteReview.bind(null,props.ID)} label="Delete"/>);
    }

    return (
        <review review_id={props.ID}>
            <a
            tooltip-top={props.self ? "Story Author" : null}
            href={props.name === "Anonymous" ? null : "/" + props.name}
            className={"author" + (props.self ? ' book-author' : '') }
            >
                {props.name}
            </a>
            <review-time>{props.time}</review-time>
            <quote>{props.quote}</quote>
            <review-content>{props.content}</review-content>
            <Dropdown right children={fill}/>
            {props.replies}
        </review>
    )
}
const map_reviews = reviewObj => {
    return (
        <Review
        key={reviewObj.ID}
        ID={reviewObj.ID}
        self={reviewObj.user.self}
        name={reviewObj.user.name}
        user_id={reviewObj.user.ID}
        time={reviewObj.time}
        content={reviewObj.content}
        quote={reviewObj.quote}
        can_delete={reviewObj.user.can_delete}
        can_reply={reviewObj.user.can_reply}
        replies={(reviewObj.replies || []).map(map_reviews)}
        />
    )
}
let filters = {
    sort: 'DESC',
    exclude_users: [],
    page: 1,
};
function callReviews(opts) {
    return new Promise((resolve, reject) => {
        api('get_reviews',{
            data: Object.assign(opts,{
                chapter_id
            })
        })
        .then(response => {
            if (response.code && response.code > 5) {
                resolve([],[]);
                return;
            }
            const reviewItems = response.reviews.map(map_reviews);
            const userItems = response.users.map((user) => 
                <Checkbox
                tabindex="0"
                key={user.ID}
                checked={user.checked}
                onChange={filterUsers}
                value={user.ID}
                label={user.name}
                />
            );            
            resolve([reviewItems,userItems]);
        });
    });
}
let ReviewStates;
function updateReviews() {
    return new Promise((resO,rejO) => {
        callReviews(filters)
        .then(([reviewItems,userItems]) => {
            ReviewStates.reviewItems[1](reviewItems);
            ReviewStates.userItems[1](userItems);
            resO("");
        });
    })
}
function filterUsers(event) {
    const userId = (event.target.getAttribute('value'));
    let newUsers = filters.exclude_users;
    if (event.target.checked) {
        const indexOf = filters.exclude_users.indexOf(userId);
        filters.exclude_users.splice(indexOf,1);
        newUsers = filters.exclude_users;
    }
    else {
        newUsers.push(userId);
    }
    filters.exclude_users = newUsers;
    updateReviews();
}
function setPage(to) {
    const current = parseInt(filters.page);
    to = to === 'next' ? current+1 : to;
    to = to === 'prev' ? current-1: to;
    if (to < 1) {
        return;
    }
    filters.page = to;
    updateReviews();
}
function setSort(order) {
    filters.sort = order;
    updateReviews();
}
function Reviews(props) {
    ReviewStates = {
        reviewItems: React.useState([]),
        userItems: React.useState([]),
    };
    if (ReviewStates.reviewItems[0].length === 0 && ReviewStates.userItems[0].length === 0 && filters.page === 1) {
        return null;
    }
    return (
        <view-reviews>
            <reviews-header>
                <Dropdown
                right
                children={[
                    <li tabindex="0" onClick={setSort.bind(null,'ASC')}>Oldest</li>,
                    <li tabindex="0" onClick={setSort.bind(null,'DESC')}>Newest</li>
                ]}
                />
                <Dropdown
                right
                children={ReviewStates.userItems[0]}
                />
            </reviews-header>
            <reviews empty={ReviewStates.reviewItems[0].length === 0 ? '' : null}>
                {ReviewStates.reviewItems[0]}
            </reviews>
            <reviews-pagination>
                <button onClick={setPage.bind(null,'prev')}></button>
                <button active="">{filters.page}</button>
                <button onClick={setPage.bind(null,'next')}></button>
            </reviews-pagination>
        </view-reviews>
    )
}
ReactDOM.render(
    <Reviews/>,
    DOM.q('reviews-wrapper')
);
// User Review Filter from hash
function userReviewsFromHash(){
    const hash = window.location.hash;
    if (hash.substr(0,9) !== '#reviews-') {
        return false;
    }
    let userId = parseInt(hash.substr(9));
    if (! userId) {
        return false;
    }
    DOM.q(`reviews-wrapper`).scrollIntoView();
    const excl_users = [];
    ReviewStates.userItems[0].forEach(v => {
        if (parseInt(v.key) !== userId){
            excl_users.push(v.key);
        }
    });
    filters.exclude_users = excl_users;
    updateReviews();
    return true;
}
window.addEventListener('hashchange',userReviewsFromHash);
updateReviews()
.then(userReviewsFromHash);