import { useBlockProps } from "@wordpress/block-editor";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";
import { InspectorControls } from "@wordpress/block-editor";
import { Panel, PanelBody, SelectControl } from "@wordpress/components";

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { postType, postRel } = attributes;
  const blockProps = useBlockProps();
  const [posts, setPosts] = useState([]);
  const [postTypes, setPostTypes] = useState([]);

  const fetchPosts = async () => {
    let path = "/wp/v2/posts";
    if (postType) path = path + `?type=${postType}`;
    const newPosts = await apiFetch({ path });
		const filterPosts = Object.values(newPosts).map((currentPost) => {
      return {
        label: currentPost.title.rendered,
        value: currentPost.id,
      };
    });
    setPosts(filterPosts);
  };

  const fetchPostTypes = async () => {
    const path = "/wp/v2/types/";
    const newPostTypes = await apiFetch({ path });
    const filterPostTypes = Object.values(newPostTypes).map((currentPostType) => {
      return {
        label: currentPostType.name,
        value: currentPostType.slug,
      };
    });
    setPostTypes(filterPostTypes);
  };

  useEffect(() => {
    fetchPostTypes();
  }, []);

  useEffect(() => {
    fetchPosts();
  }, [postType]);

  return (
    <>
      {postTypes.length > 0 && (
        <InspectorControls>
          <Panel>
            <PanelBody title="PostTypes" initialOpen={true}>
              <SelectControl
                label="Current PostType"
                value={postType || 1}
                options={postTypes}
                onChange={(newPosts) =>
                  setAttributes({ postType: newPosts })
                }
              />
							{posts.length > 0 && (
								<SelectControl
                label="Current Posts"
                value={postRel || 1}
                options={posts}
              	/>
							)}
            </PanelBody>
          </Panel>
        </InspectorControls>
      )}
    </>
  );
};

export default Edit;
