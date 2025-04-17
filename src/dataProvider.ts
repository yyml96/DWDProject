import simpleRestDataProvider from "ra-data-simple-rest";
import { addEventsForMutations } from "@blackbox-vision/ra-audit-log";
import {
  CreateParams,
  UpdateParams,
  DataProvider,
  fetchUtils,
} from "react-admin";
import { authProvider } from "./authProvider";

const endpoint = "http://localhost:8098/backend/api";
const baseDataProvider = simpleRestDataProvider(endpoint);

type PostParams = {
    id: string;
  userId: string;
  assignedTo: string;
  title: string;
  body: string;
  image: {
    rawFile: File;
    src?: string;
    title?: string;
  };
  video: {
    rawFile: File;
    src?: string;
    title?: string;
  };
};

const createPostFormData = (
  params: CreateParams<PostParams> | UpdateParams<PostParams>
) => {
  const formData = new FormData();
  params.data.image?.rawFile && formData.append("image", params.data.image.rawFile);
  params.data.video?.rawFile && formData.append("video", params.data.video.rawFile);
  params.data.title && formData.append("title", params.data.title);
  params.data.body && formData.append("body", params.data.body);
  params.data.userId && formData.append("userId", params.data.userId);
  params.data.assignedTo && formData.append("assignedTo", params.data.assignedTo);

  return formData;
};

export const dataProvider: DataProvider = addEventsForMutations(
{
    ...baseDataProvider,
    create: (resource, params) => {
      if (resource === "posts") {
        const formData = createPostFormData(params);
        return fetchUtils
          .fetchJson(`${endpoint}/${resource}`, {
            method: "POST",
            body: formData,
          })
          .then(({ json }) => ({ data: json }));
      }

      return baseDataProvider.create(resource, params);
    },
    update: (resource, params) => {
      if (resource === "posts") {
        const formData = createPostFormData(params);
        formData.append("id", params.id);
        return fetchUtils
          .fetchJson(`${endpoint}/${resource}`, {
            method: "PUT",
            body: formData,
          })
          .then(({ json }) => ({ data: json }));
      }

      return baseDataProvider.update(resource, params);
    },
  },
  authProvider,
  {
    name: "audit_logs",
    //resources: ["posts", "users"],
    shouldAudit: (action, resource) => {
      return action !== "getList";
    },
  }
);