// (() => {
    const {useCallback,useState,createRef,useEffect} = React;

    class TextInput extends React.Component {
        constructor(props) {
            super(props);
            this.handleChange = props.Type === 'textarea' ?
            event => {
                props.onChange(event.target.value);
                reRender();
                if (event.target.value === ''){
                    event.target.classList.remove('filled');
                    event.target.style.height = '43px';
                    return;
                }
                event.target.classList.add('filled');
                event.target.style.height = 'auto';
                event.target.style.height = event.target.scrollHeight + 'px';
            } :
            event => {
                props.onChange(event.target.value);
                reRender();
                if (event.target.value === ''){
                    event.target.classList.remove('filled');
                    return;
                }
                event.target.classList.add('filled');
            };
        }
        render () {
            const {
                Type = 'input',
                input_type = 'text',
                label,
                value = '',
                maxlength
            } = this.props;
            return (
                <text-input>
                    <Type
                    type={input_type}
                    value={value}
                    onChange={this.handleChange}
                    maxlength={maxlength ? maxlength : null}
                    />
                    <label>{label}</label>
                </text-input>
            )
        }
    }

    let route,setRoute;

    const Index = ({topics}) => {
        const children = topics.map(({name,link}) => (
            <div
            onClick={() => setRoute([link])}
            >
                {name}
            </div>
        ));
        return <div className="feedback-index">{children}</div>;
    }
    const NewFeature =  ({freeze}) => {
        return (
            <div className="new-feature">
                <TextInput label="Title"/>
                <TextInput
                Type="textarea"
                label="Any details..."
                />
                <button disabled={freeze} label="Submit"></button>
            </div>
        );
    }
    const RoundBox = ({count,className,active=false}) => {
        return (
            <div
            active={active ? "" : null}
            className={className + " item-stat"}
            >
                <div/>
                <div>{count}</div>
            </div>
        ) 
    }
    
    const FeedItem = ({title,description,votes,comments,voted,link}) => {
        return (
            <Link to={[route[0],link]}>
                <div className="feed-single">
                    <div>
                        <RoundBox active={voted} count={votes} className="votes"/>
                        <RoundBox count={comments.length} className="comments"/>
                    </div>
                    <div>
                        <h3>{title}</h3>
                        <div>{description}</div>
                    </div>
                </div>
            </Link>
        );
    }
    const Comment = ({by,comment}) => {
        return (
            <div className="comment-single">
                <div>
                    <RoundBox count={0} className="votes"/>
                </div>
                <div>
                    <div>{by}</div>
                    <div>{comment}</div>
                </div>
            </div>
        );
    }
    const CommentsFeed = ({comments}) => {
        return (
            <div className="comments-feed">
            {comments.map(Comment)}
            </div>
        );
    }
    const WriteComment = () => {
        return (
            <div className="write-comment">
                <TextInput label="What do you think?"/>
                <button label="Submit"/>
            </div>
        )
    }
    const FullItem = ({item}) => {
        return (
            <div>
                <Link to={[route[0]]}>Back</Link>
                {!item ?
                    <NothingFound back={false}/> : (
                    <div>
                        <FeedItem {...item}/>
                        <CommentsFeed comments={item.comments}/>
                        <WriteComment/>
                    </div>
                )}
            </div>
        );
    }
    const Feed = ({loading,feed}) => {
        return (
            <div class="feed">
                {loading ? <loader xs=""/> : feed.map(FeedItem)}
            </div>
        );
    }
    const NothingFound  = ({back = true}) => {

        return (
            <div>
                <div>Sorry, there's nothing here.</div>
                {!back ? [] : <div>Check out all open topics <Link to={[]}>here</Link>.</div>}
            </div>
        )
    }
    const Topic = ({topic,loading,feed}) => {
        const inner = (
            !topic ?
            <NothingFound/> : (
            <div className="feedback-topic">
                <NewFeature freeze={loading}/>
                <Feed
                loading={loading}
                feed={feed}
                />
            </div>
            )
        );
        return (
            <div>
                <Link to={[]}>Back</Link>
            {inner}
            </div>
        );
    }
    const routeURL = (_route = route) => "/feedback/" + _route.join("/");
    
    const Link = ({to,children}) => (
        <a
        className="no-style"
        href={routeURL(to)}
        onClick={e => {
            e.preventDefault();
            if (to !== route) {
                setRoute(to);
            }
        }}
        >{children}</a>
    );

    function App () {
        const topics = [{
            name: "Feed",
            link: "feed"
        }];
        const feed = {feed: [
            {
                title: "Ability to filter by pairings",
                description: "Aside from characters, there should be an option to filter by pairings, so people who like specific ships can get those stories.",
                votes: 124,
                comments: [{
                    by: "@admin",
                    comment: "I don't think so, that would mess that up"
                },{
                    by: "@brad",
                    comment: "That would be amazing!"
                }],
                voted: true,
                link: "filter-pairing"
            },
            {
                title: "Disable tags permanently.",
                description: "An option to disable specific tags permanently.",
                votes: 64,
                comments: [{
                    by: "@admin",
                    comment: "I don't think so, that would mess that up"
                },{
                    by: "@brad",
                    comment: "That would be amazing!"
                }],
                voted: false,
                link: "disable-tags-permanently"
            },
        ]};
    
        [route,setRoute] = useState(window.location.pathname.split('/').filter(v => v !== "").slice(1));
        const [loading,setLoading] = useState(false);

        // Route Set
        useEffect(() => {
            window.history.pushState({}, DOM.q("title").innerText,routeURL());
        },[route]);

        switch (route.length) {
            case 0:
                return <Index topics={topics}/>;
            case 1:
                return (
                    <Topic
                    topic={topics.find(({link}) => link === route[0])}
                    loading={loading}
                    feed={feed[route[0]] || []}
                    />
                );
            case 2:
                return (
                    <FullItem
                    item={(feed[route[0]] || []).find(({link}) => link === route[1])}
                    />
                );
            default:
                return <NothingFound/>
        }
    }
    ReactDOM.render(
        <App/>,
        DOM.q('main')
    );
// })();