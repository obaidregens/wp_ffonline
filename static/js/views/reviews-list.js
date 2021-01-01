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
      }
    }).then(response => {
      if (response.code > 5) {
        new toast('An error occured.');
      }

      updateReviews();
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
    href: props.name === "Anonymous" ? null : "/" + props.name,
    className: "author" + (props.self ? ' book-author' : '')
  }, props.name), /*#__PURE__*/React.createElement("review-time", null, props.time), /*#__PURE__*/React.createElement("quote", null, props.quote), /*#__PURE__*/React.createElement("review-content", null, props.content), /*#__PURE__*/React.createElement(Dropdown, {
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
    quote: reviewObj.quote,
    can_delete: reviewObj.user.can_delete,
    can_reply: reviewObj.user.can_reply,
    replies: (reviewObj.replies || []).map(map_reviews)
  });
};

let filters = {
  sort: 'DESC',
  exclude_users: [],
  page: 1
};

function callReviews(opts) {
  return new Promise((resolve, reject) => {
    api('get_reviews', {
      data: Object.assign(opts, {
        chapter_id
      })
    }).then(response => {
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
    });
  });
}

let ReviewStates;

function updateReviews() {
  return new Promise((resO, rejO) => {
    callReviews(filters).then(([reviewItems, userItems]) => {
      ReviewStates.reviewItems[1](reviewItems);
      ReviewStates.userItems[1](userItems);
      resO("");
    });
  });
}

function filterUsers(event) {
  const userId = event.target.getAttribute('value');
  let newUsers = filters.exclude_users;

  if (event.target.checked) {
    const indexOf = filters.exclude_users.indexOf(userId);
    filters.exclude_users.splice(indexOf, 1);
    newUsers = filters.exclude_users;
  } else {
    newUsers.push(userId);
  }

  filters.exclude_users = newUsers;
  updateReviews();
}

function setPage(to) {
  const current = parseInt(filters.page);
  to = to === 'next' ? current + 1 : to;
  to = to === 'prev' ? current - 1 : to;

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
    userItems: React.useState([])
  };

  if (ReviewStates.reviewItems[0].length === 0 && ReviewStates.userItems[0].length === 0 && filters.page === 1) {
    return null;
  }

  return /*#__PURE__*/React.createElement("view-reviews", null, /*#__PURE__*/React.createElement("reviews-header", null, /*#__PURE__*/React.createElement(Dropdown, {
    right: true,
    children: [/*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      onClick: setSort.bind(null, 'ASC')
    }, "Oldest"), /*#__PURE__*/React.createElement("li", {
      tabindex: "0",
      onClick: setSort.bind(null, 'DESC')
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
  }, filters.page), /*#__PURE__*/React.createElement("button", {
    onClick: setPage.bind(null, 'next')
  })));
}

ReactDOM.render( /*#__PURE__*/React.createElement(Reviews, null), DOM.q('reviews-wrapper')); // User Review Filter from hash

function userReviewsFromHash() {
  const hash = window.location.hash;

  if (hash.substr(0, 9) !== '#reviews-') {
    return false;
  }

  let userId = parseInt(hash.substr(9));

  if (!userId) {
    return false;
  }

  _scroll.to(DOM.q(`reviews-wrapper`));
  const excl_users = [];
  ReviewStates.userItems[0].forEach(v => {
    if (parseInt(v.key) !== userId) {
      excl_users.push(v.key);
    }
  });
  filters.exclude_users = excl_users;
  updateReviews();
  return true;
}

window.addEventListener('hashchange', userReviewsFromHash);
updateReviews().then(userReviewsFromHash);