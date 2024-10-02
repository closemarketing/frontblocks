import { useBlockProps } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";
import { InspectorControls } from "@wordpress/block-editor";
import { Panel, PanelBody, SelectControl } from "@wordpress/components";

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { category } = attributes;
  const blockProps = useBlockProps();
  const [posts, setPosts] = useState([]);
  const [categories, setPostTypes] = useState([]);

  const fetchPosts = async () => {
    let path = "/wp/v2/posts";
    if (category) path = path + `?categories=${category}`;
    const newPosts = await apiFetch({ path });
    setPosts(newPosts);
  };

  const fetchPostTypes = async () => {
    const path = "/wp/v2/types/?hide_empty=true";
    const newPostTypes = await apiFetch({ path });
    const filterPostTypes = newPostTypes.map((currentPostType) => {
      return {
        label: currentPostType.name,
        value: currentPostType.id,
      };
    });
    setPostTypes(filterPostTypes);
  };

  useEffect(() => {
    fetchPostTypes();
  }, []);

  useEffect(() => {
    fetchPosts();
  }, [category]);

  return (
    <>
      {categories.length > 0 && (
        <InspectorControls>
          <Panel>
            <PanelBody title="PostTypes" initialOpen={true}>
              <SelectControl
                label="Current PostType"
                value={category || 1}
                options={categories}
                onChange={(newPostType) =>
                  setAttributes({ category: newPostType })
                }
              />
            </PanelBody>
          </Panel>
        </InspectorControls>
      )}
      {posts.length > 0 && (
        <div {...blockProps}>
          <h3>Quizás te interese leer esto:</h3>
          <ul className="posts">
            {posts.map((post) => {
              return (
                <li key={post.id}>
                  <a href={post.link}>{post.title.rendered}</a>
                </li>
              );
            })}
          </ul>
        </div>
      )}
    </>
  );
};

export default Edit;
