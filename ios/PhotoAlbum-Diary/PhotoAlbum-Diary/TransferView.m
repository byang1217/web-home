//
//  ViewController.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "TransferView.h"
#import "ScanerVC.h"
#import "SetupView.h"

enum {
    TX_UPLOAD = 0,
    TX_DOWNLOAD,
    TX_NUM
};

enum {
    TX_UPLOAD_INFO = 0,
    TX_UPLOAD__NUM,
};

enum {
    TX_DOWNLOAD_INFO = 0,
    TX_DOWNLOAD_NUM,
};


@interface CellButton : UIButton
@property NSIndexPath *indexPath;
@end
@implementation CellButton
@end


@interface TransferView ()
@property BOOL navigationBarHiddenSave;

@property UIImageView *connectingImageView;

@property UITableViewCell *uploadCell;
@property UIProgressView *uploadProgress;
@property CellButton *uploadButton;

@end

@implementation TransferView

- (void)viewDidLoad {
    self.tableView.tableFooterView = [UIView new];
    [super viewDidLoad];
}

-(void) viewWillAppear:(BOOL)animated {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;
    
    defaultAlbumSync.delegate = self;
    AlbumSync_Start();
    [self.tableView reloadData];
}

-(void) viewWillDisappear:(BOOL)animated {
    self.uploadCell = nil;
    self.uploadButton = nil;
    self.uploadProgress = nil;
    defaultAlbumSync.delegate = nil;

    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}

- (void)showUploadConnecting
{
    UITableViewCell *cell = self.uploadCell;
    
    NSMutableArray *imagesArray = [[NSMutableArray alloc] init];
    for (int i = 0; i < 12; i++) {
        NSString *imgName = [[NSString alloc] initWithFormat:@"loading_%d.gif", i];
        UIImage *img = [UIImage imageNamed:imgName];
        UIImage *resizedImg = MyLib_ImageWithImage(img, CGSizeMake(cell.textLabel.font.lineHeight * 3, cell.textLabel.font.lineHeight * 3));
        [imagesArray setObject:resizedImg atIndexedSubscript:i];
    }
    cell.imageView.image = imagesArray[0];
    cell.imageView.animationImages = imagesArray;
    cell.imageView.animationDuration = 1.0f;
    cell.imageView.animationRepeatCount = 0;
    [cell.imageView startAnimating];
}

- (void)showUploadStop
{
    UITableViewCell *cell = self.uploadCell;

    cell.imageView.image = nil;
    cell.imageView.animationImages = nil;

    UIImage *orgImg = [UIImage imageNamed:@"stop_red.png"];
    UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight * 3, cell.textLabel.font.lineHeight * 3));
    cell.imageView.image = resizedImg;
}

- (void)showUploadOngoing:(PHAsset *)asset
{
    UITableViewCell *cell = self.uploadCell;
    
    MyInf(@"fetch thumb image ...");
    PHImageRequestOptions *Options = [[PHImageRequestOptions alloc] init];
    Options.networkAccessAllowed = YES;
    
    CGSize AssetThumbnailSize = CGSizeMake(cell.textLabel.font.lineHeight * 3, cell.textLabel.font.lineHeight * 3);
    [[PHImageManager defaultManager]
     requestImageForAsset:asset
     targetSize:AssetThumbnailSize
     contentMode:PHImageContentModeDefault
     options:Options
     resultHandler:^(UIImage *result, NSDictionary *info) {
         if ([[info objectForKey:PHImageCancelledKey] boolValue]) {
             MyErr(@"cancel image request\n");
         }else if ([info objectForKey:PHImageErrorKey]) {
             MyErr(@"Image request Error\n");
         }else if ([[info objectForKey:PHImageResultIsDegradedKey] boolValue]) {
             MyDbg(@"Image request low quality\n");
             cell.imageView.image = nil;
             cell.imageView.animationImages = nil;
             cell.imageView.image = result;
         }else if ([[info objectForKey:PHImageResultIsInCloudKey] boolValue]) {
             MyErr(@"Image request in cloud. it should not happen since we enabled the network option\n");
         }else {
             MyDbg(@"Image request High quality\n");
             cell.imageView.image = nil;
             cell.imageView.animationImages = nil;
             cell.imageView.image = result;
         }
     }
     ];
}

- (void)showUploadComplete
{
    UITableViewCell *cell = self.uploadCell;
    
    cell.imageView.image = nil;
    cell.imageView.animationImages = nil;

    UIImage *orgImg = [UIImage imageNamed:@"done.png"];
    UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight * 3, cell.textLabel.font.lineHeight * 3));
    cell.imageView.image = resizedImg;
}


#pragma mark - UITableViewDataSource

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if (!sender)
        return;

    UITableViewCell *cell = sender;
    NSIndexPath *indexPath = [self.tableView indexPathForCell:cell];
    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;

}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return TX_NUM;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    NSInteger numberOfRows = 0;
    
    if (section == TX_UPLOAD) {
        numberOfRows = 1;
    }
    if (section == TX_DOWNLOAD) {
        numberOfRows = 0;
    }
    return numberOfRows;
}

- (NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section {
    if (section == TX_UPLOAD) {
        return @"Upload";
    }
    if (section == TX_DOWNLOAD) {
        return @"Download";
    }
    return @"";
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    UITableViewCell *cell = nil;

    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;
    
    if (section == TX_UPLOAD) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"TxUploadCell" forIndexPath:indexPath];
        self.uploadCell = cell;

        if (row == TX_UPLOAD_INFO) {
            cell.textLabel.numberOfLines = 2;
            cell.textLabel.lineBreakMode = NSLineBreakByTruncatingTail;
            
            CellButton *btn = [CellButton buttonWithType:UIButtonTypeCustom];
            self.uploadButton = btn;
            btn.indexPath = indexPath;
            if (setup_get_bool(SETUP_KEY_UPLOAD_EN)) {
                [self showUploadConnecting];
                cell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_CONNECTING")];
                [btn setImage:[UIImage imageNamed:@"pause_black"] forState:UIControlStateNormal];
            }else {
                [self showUploadStop];
                cell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_STOPPED")];
                [btn setImage:[UIImage imageNamed:@"start_black.png"] forState:UIControlStateNormal];
            }
            [btn addTarget:self action:@selector(buttonClick:) forControlEvents:UIControlEventTouchUpInside];
            btn.frame = CGRectMake(cell.frame.size.width - cell.textLabel.font.lineHeight * 1.5, cell.textLabel.font.lineHeight * 0.5, cell.textLabel.font.lineHeight * 1.2, cell.textLabel.font.lineHeight * 1.2);
            [cell.contentView addSubview:btn];
            
            UIProgressView *progress = [[UIProgressView alloc] initWithProgressViewStyle:UIProgressViewStyleDefault];
            self.uploadProgress = progress;
            progress.frame = CGRectMake(cell.textLabel.font.lineHeight * 3.5, cell.textLabel.font.lineHeight * 2.0, cell.frame.size.width - cell.textLabel.font.lineHeight * 6.0, cell.textLabel.font.lineHeight);
            progress.progress = 0;
            progress.hidden = YES;
            [cell.contentView addSubview:progress];
            
            cell.selectionStyle = UITableViewCellSelectionStyleNone;

            

//            cell.accessoryType = UITableViewCellAccessoryDisclosureIndicator; /* add arrow to the right */
            
        }
    }

    if (section == TX_DOWNLOAD) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"TxDownloadCell" forIndexPath:indexPath];
    }

    return cell;
}

- (void) tableView: (UITableView *) tableView didSelectRowAtIndexPath: (NSIndexPath *) indexPath {
    MyInf(@"click me: %@", indexPath);
     NSInteger section = indexPath.section;
     NSInteger row = indexPath.row;
}

             
- (void)buttonClick:(CellButton *)sender
{
    NSInteger section = sender.indexPath.section;
    NSInteger row = sender.indexPath.row;

    if (section == TX_UPLOAD && row == TX_UPLOAD_INFO) {
        setup_set_bool(SETUP_KEY_UPLOAD_EN, !setup_get_bool(SETUP_KEY_UPLOAD_EN));

        if (setup_get_bool(SETUP_KEY_UPLOAD_EN)) {
            [self showUploadConnecting];
            [self.uploadButton setImage:[UIImage imageNamed:@"pause_black"] forState:UIControlStateNormal];
            self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_CONNECTING")];
        }else {
            [self showUploadStop];
            [self.uploadButton setImage:[UIImage imageNamed:@"start_black.png"] forState:UIControlStateNormal];
            self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_STOPPED")];
        }

//        [self.tableView reloadData];
        AlbumSync_reset();
    }
}

#pragma mark - AlbumSync Delegate
-(void)statusUpdateHandler:(NSDictionary *)statusInfDict
{
    NSString *status = statusInfDict[@"status"];
    
    MyInf(@"status: %@", status);
    if ([status compare:@"connecting"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_CONNECTING")];
        [self showUploadConnecting];
        self.uploadProgress.hidden = YES;
    }else if ([status compare:@"complete"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_COMPLETE")];
        [self showUploadComplete];
        self.uploadProgress.hidden = YES;
    }else if([status compare:@"stop"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_STOPPED")];
        [self showUploadStop];
        self.uploadProgress.hidden = YES;
    }else if([status compare:@"error"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_ERROR")];
        [self showUploadConnecting];
        self.uploadProgress.hidden = YES;
    }else if([status compare:@"uploading"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_UPLOADING")];
        [self showUploadConnecting];
        self.uploadProgress.hidden = YES;
    }else if([status compare:@"uploading_albums"] == NSOrderedSame) {
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@", LOCAL_STRING(@"STATUS_UPLOADING_ALBUMS")];
        [self showUploadConnecting];
        self.uploadProgress.hidden = YES;
    }else if([status compare:@"uploading_file"] == NSOrderedSame) {
        MyInf(@"uploading_file: %@", statusInfDict[@"file_name"]);
        self.uploadCell.textLabel.text = [[NSString alloc] initWithFormat:@"%@ (%@)", statusInfDict[@"file_name"], statusInfDict[@"file_size"]];
        PHAsset *asset = statusInfDict[@"asset"];
        [self showUploadOngoing:asset];
        self.uploadProgress.hidden = NO;
        self.uploadProgress.progress = 0;
    }else if([status compare:@"uploading_progress"] == NSOrderedSame) {
        NSNumber *progressNum = [statusInfDict valueForKey:@"progress"];
        if (progressNum) {
            self.uploadProgress.hidden = NO;
            self.uploadProgress.progress = [progressNum floatValue];
        }
    }else if([status compare:@"uploading_albums_progress"] == NSOrderedSame) {
        NSNumber *progressNum = [statusInfDict valueForKey:@"progress"];
        if (progressNum) {
            self.uploadProgress.hidden = NO;
            self.uploadProgress.progress = [progressNum floatValue];
        }
    }else {
        MyErr(@"invalid upload status: %@", status);
    }
}




@end
