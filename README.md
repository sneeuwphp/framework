# sneeuw

Sneeuw is a modern web framework that makes it easy to write efficient and highly dynamic full-stack web applications. It does that by providing you (the developer) with tools such as file-based routing, server-side rendering, fine-grained reactivity and much more...

By default, everything is server-side rendered and no JavaScript is sent to the browser. Only when you opt-in, will Sneeuw generate a JavaScript bundle for the client and hydrate your components.

Sneeuw applications consist of back-end logic written using a language none other than ✨ PHP ✨ (but supercharged by static-analyzers to bring more strict and accurate types) and front-end logic written using TypeScript and JSX (with a custom runtime inspired by frameworks like SolidJS.) Sneeuw glues these two technologies together and allows for seamless and efficient communication between the two. This makes writing efficient and dynamic full-stack web applications a breeze.

## roadmap

Sneeuw is currently in MVP status. Core parts of the framework are still in the design process or are actively being developed.

### 0.1 | MVP

The goal for this release is to implement what makes Sneeuw, Sneeuw. To see if this idea actually works and consists of the following components:

- [ ] File-based and traditional router
- [ ] Front-end solution with fine-grained reactivity, server-side rendering and hydration
- [ ] Single file components with seamless communication between back-end and front-end
- [ ] Basic database/model setup

With these components we should be able to create an efficient and dynamic todo demo application.
