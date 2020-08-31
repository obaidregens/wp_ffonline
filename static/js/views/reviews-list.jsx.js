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
            },
            dataType: 'JSON',
            callback: response => {
                if (response.code > 5) {
                    new toast('An error occured.');
                }
                updateReviews();
            }
        });
    });
}
function Review (props) {
    const fill = [];
    if (props.can_reply) {
        fill.push(<li label="Reply"/>);
    }
    if (props.can_delete) {
        fill.push(<li onClick={deleteReview.bind(null,props.ID)} label="Delete"/>);
    }

    return (
        <review review_id={props.ID}>
            <a
            tooltip-top={props.self ? "Story Author" : null}
            className={"author" + (props.self ? ' book-author' : '') }
            >
                {props.name}
            </a>
            <review-time>{props.time}</review-time>
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
        can_delete={reviewObj.user.can_delete}
        can_reply={reviewObj.user.can_reply}
        replies={(reviewObj.replies || []).map(map_reviews)}
        />
    )
}
function callReviews(opts) {
    return new Promise((resolve, reject) => {
        opts.chapter_id = chapter_id;
        api('get_reviews',{
            dataType: 'JSON',
            data: opts,
            callback: response => {
                if (response.code && response.code > 5) {
                    resolve([],[]);
                    return;
                }
                const reviewItems = response.reviews.map(map_reviews);
                const userItems = response.users.map((user) => 
                    <Checkbox
                    key={user.ID}
                    checked={user.checked}
                    onChange={filterUsers}
                    value={user.ID}
                    label={user.name}
                    />
                );            
                resolve([reviewItems,userItems]);
            }
        });
    });
}
let ReviewStates;
function updateReviews() {
    callReviews({
        sort: ReviewStates.sort[0],
        exclude_users: ReviewStates.exclude_users[0],
        page: ReviewStates.page[0]
    })
    .then(([reviewItems,userItems]) => {
        ReviewStates.reviewItems[1](reviewItems);
        ReviewStates.userItems[1](userItems);
    });
}
function filterUsers(event) {
    const userId = (event.target.getAttribute('value'));
    let newUsers = ReviewStates.exclude_users[0];
    if (event.target.checked) {
        const indexOf = ReviewStates.exclude_users[0].indexOf(userId);
        ReviewStates.exclude_users[0].splice(indexOf,1);
        newUsers = ReviewStates.exclude_users[0];
    }
    else {
        newUsers.push(userId);
    }
    ReviewStates.exclude_users[1](newUsers);
    updateReviews();
}
function setPage(to) {
    const current = parseInt(ReviewStates.page[0]);
    to = to === 'next' ? current+1 : to;
    to = to === 'prev' ? current-1: to;
    if (to < 1) {
        return;
    }
    ReviewStates.page[1](to);
}
function Reviews(props) {
    ReviewStates = {
        sort: React.useState('DESC'),
        exclude_users: React.useState([]),
        page: React.useState([1]),
        reviewItems: React.useState([]),
        userItems: React.useState([]),
    };
    React.useEffect(updateReviews,[ReviewStates.page[0],ReviewStates.sort[0],ReviewStates.exclude_users[0]]);
    if (ReviewStates.reviewItems[0].length === 0) {
        return null;
    }
    return (
        <view-reviews>
            <reviews-header>
                <Dropdown
                right
                children={[
                    <li onClick={ReviewStates.sort[1].bind(null,'ASC')}>Oldest</li>,
                    <li onClick={ReviewStates.sort[1].bind(null,'DESC')}>Newest</li>
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
                <button active="">{ReviewStates.page[0]}</button>
                <button onClick={setPage.bind(null,'next')}></button>
            </reviews-pagination>
        </view-reviews>
    )
}
ReactDOM.render(
    <Reviews/>,
    document.querySelector('reviews-wrapper')
);
updateReviews();