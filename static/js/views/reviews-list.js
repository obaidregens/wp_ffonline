"use strict";

function Checkbox(props) {
  return /*#__PURE__*/React.createElement("label", {
    class: "checkbox"
  }, /*#__PURE__*/React.createElement("input", {
    checked: props.checked,
    onChange: props.onChange,
    type: "checkbox",
    value: props.value || props.label
  }), /*#__PURE__*/React.createElement("text", null, props.label));
}

function Dropdown(props) {
  if (props.children.length === 0) {
    return null;
  }

  return /*#__PURE__*/React.createElement("button", {
    label: props.label,
    theme: props.theme,
    className: "dropdown"
  }, /*#__PURE__*/React.createElement("dropdown", {
    className: props.right ? 'right' : ''
  }, props.children));
}

function deleteReview(review_id) {
  confirmation('Are you want to delete this review?').then(res => {
    if (!res) {
      return;
    }

    api('delete_review', {
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

function Review(props) {
  const fill = [];

  if (props.can_reply) {
    fill.push( /*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      label: "Reply"
    }));
  }

  if (props.can_delete) {
    fill.push( /*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      onClick: deleteReview.bind(null, props.ID),
      label: "Delete"
    }));
  }

  return /*#__PURE__*/React.createElement("review", {
    review_id: props.ID
  }, /*#__PURE__*/React.createElement("a", {
    "tooltip-top": props.self ? "Story Author" : null,
    className: "author" + (props.self ? ' book-author' : '')
  }, props.name), /*#__PURE__*/React.createElement("review-time", null, props.time), /*#__PURE__*/React.createElement("review-content", null, props.content), /*#__PURE__*/React.createElement(Dropdown, {
    right: true,
    children: fill
  }), props.replies);
}

const map_reviews = reviewObj => {
  return /*#__PURE__*/React.createElement(Review, {
    key: reviewObj.ID,
    ID: reviewObj.ID,
    self: reviewObj.user.self,
    name: reviewObj.user.name,
    user_id: reviewObj.user.ID,
    time: reviewObj.time,
    content: reviewObj.content,
    can_delete: reviewObj.user.can_delete,
    can_reply: reviewObj.user.can_reply,
    replies: (reviewObj.replies || []).map(map_reviews)
  });
};

function callReviews(opts) {
  return new Promise((resolve, reject) => {
    opts.chapter_id = chapter_id;
    api('get_reviews', {
      dataType: 'JSON',
      data: opts,
      callback: response => {
        if (response.code && response.code > 5) {
          resolve([], []);
          return;
        }

        const reviewItems = response.reviews.map(map_reviews);
        const userItems = response.users.map(user => /*#__PURE__*/React.createElement(Checkbox, {
          tabindex: "0",
          key: user.ID,
          checked: user.checked,
          onChange: filterUsers,
          value: user.ID,
          label: user.name
        }));
        resolve([reviewItems, userItems]);
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
  }).then(([reviewItems, userItems]) => {
    ReviewStates.reviewItems[1](reviewItems);
    ReviewStates.userItems[1](userItems);
  });
}

function filterUsers(event) {
  const userId = event.target.getAttribute('value');
  let newUsers = ReviewStates.exclude_users[0];

  if (event.target.checked) {
    const indexOf = ReviewStates.exclude_users[0].indexOf(userId);
    ReviewStates.exclude_users[0].splice(indexOf, 1);
    newUsers = ReviewStates.exclude_users[0];
  } else {
    newUsers.push(userId);
  }

  ReviewStates.exclude_users[1](newUsers);
  updateReviews();
}

function setPage(to) {
  const current = parseInt(ReviewStates.page[0]);
  to = to === 'next' ? current + 1 : to;
  to = to === 'prev' ? current - 1 : to;

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
    userItems: React.useState([])
  };
  React.useEffect(updateReviews, [ReviewStates.page[0], ReviewStates.sort[0], ReviewStates.exclude_users[0]]);

  if (ReviewStates.reviewItems[0].length === 0) {
    return null;
  }

  return /*#__PURE__*/React.createElement("view-reviews", null, /*#__PURE__*/React.createElement("reviews-header", null, /*#__PURE__*/React.createElement(Dropdown, {
    right: true,
    children: [/*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      onClick: ReviewStates.sort[1].bind(null, 'ASC')
    }, "Oldest"), /*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      onClick: ReviewStates.sort[1].bind(null, 'DESC')
    }, "Newest")]
  }), /*#__PURE__*/React.createElement(Dropdown, {
    right: true,
    children: ReviewStates.userItems[0]
  })), /*#__PURE__*/React.createElement("reviews", {
    empty: ReviewStates.reviewItems[0].length === 0 ? '' : null
  }, ReviewStates.reviewItems[0]), /*#__PURE__*/React.createElement("reviews-pagination", null, /*#__PURE__*/React.createElement("button", {
    onClick: setPage.bind(null, 'prev')
  }), /*#__PURE__*/React.createElement("button", {
    active: ""
  }, ReviewStates.page[0]), /*#__PURE__*/React.createElement("button", {
    onClick: setPage.bind(null, 'next')
  })));
}

ReactDOM.render( /*#__PURE__*/React.createElement(Reviews, null), document.querySelector('reviews-wrapper'));
updateReviews();