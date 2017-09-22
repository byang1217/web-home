//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "AppDelegate.h"
#import "ShortBeat.h"

ShortBeat *defaultShortBeat;

@interface ShortBeat ()

@property NSUInteger transfer_id;

@end

@implementation ShortBeat

void ShortBeat_Start(void)
{
    AlbumSync_exeOnActionWorkQueue(^{
        if (!defaultShortBeat) {
            defaultShortBeat = [[ShortBeat alloc] init];
            [defaultShortBeat initBackgroundSession:@"shortBeat" timeout:100];
        }

        if (!defaultAlbumSync.uploadOnGoing) {
            MyInf(@"upload is not ongoing, ignore");
        }else {
            NSMutableDictionary *urlParams = [[NSMutableDictionary alloc] init];
            [urlParams setObject:MyLib_UnixTimeUsString() forKey:@"transfer_id"];
            [urlParams setObject:@"ShortBeat" forKey:@"command"];
            MyInf(@"url=%@, params=%@\n", defaultAlbumSync.uploadServerURLString, urlParams);
            [defaultShortBeat getDataBackground:defaultAlbumSync.uploadServerURLString urlParams:urlParams];
        }
    });
}

#pragma mark - NSURLSession
- (void)URLSession:(NSURLSession *)session task:(NSURLSessionTask *)task didCompleteWithError:(NSError *)error
{
    MyInf(@"didCompleteWithError, error=%@\n", error);
    ShortBeat_Start();
}

- (void)URLSession:(NSURLSession *)session dataTask:(NSURLSessionDataTask *)dataTask didReceiveData:(NSData *)data
{
    MyDbg(@"didReceiveData: %@\n", [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding]);
}

/*
 If an application has received an -application:handleEventsForBackgroundURLSession:completionHandler: message, the session delegate will receive this message to indicate that all messages previously enqueued for this session have been delivered. At this time it is safe to invoke the previously stored completion handler, or to begin any internal updates that will result in invoking the completion handler.
 */
- (void)URLSessionDidFinishEventsForBackgroundURLSession:(NSURLSession *)session
{
    MyInf(@"session URLSessionDidFinishEventsForBackgroundURLSession\n");
    ShortBeat_Start();
/*
    AppDelegate *appDelegate = (AppDelegate *)[[UIApplication sharedApplication] delegate];
    if (global_background_mode && appDelegate.backgroundSessionCompletionHandler) {
        void (^completionHandler)() = appDelegate.backgroundSessionCompletionHandler;
        appDelegate.backgroundSessionCompletionHandler = nil;
        MyInf(@"call completionHandler");
        completionHandler();
    }
 */
}

@end

